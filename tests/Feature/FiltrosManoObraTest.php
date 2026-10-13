<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\DetalleManoObra;
use App\Models\ManoObra;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Filtros del reporte de mano de obra.
 *
 * mano_obra no tiene proveedor_id (el proveedor vive en detalle_mano_obra) ni
 * columna fecha (el periodo es fecha_inicio/fecha_fin), asi que estos filtros
 * rompian la consulta con "Unknown column".
 */
class FiltrosManoObraTest extends TestCase
{
    use RefreshDatabase;

    private array $contexto = [];

    private function crearContexto(): void
    {
        $user = User::factory()->create(['usuario' => 'usuario_filtros_orden_test']);
        $etapa = CatalogoDato::create(['descripcion' => 'Etapa filtros mo', 'slug' => 'etapa-filtros-mo', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa filtros mo', 'slug' => 'tipo-etapa-filtros-mo', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto filtros mo', 'slug' => 'catalogo-proyecto-filtros-mo', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto filtros mo', 'slug' => 'estado-proyecto-filtros-mo', 'activo' => true]);
        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo filtros mo', 'slug' => 'categoria-articulo-filtros-mo', 'activo' => true]);
        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor filtros mo', 'slug' => 'categoria-proveedor-filtros-mo', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto filtros mo',
            'nombre_propietario' => 'Propietario filtros mo',
            'telefono' => '0999999999',
            'correo' => 'filtros-mo@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-FMO',
            'descripcion' => 'Oficial',
            'activo' => true,
        ]);

        $proveedorAlfa = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999101',
            'razon_social' => 'Alfa proveedor',
            'direccion' => 'Direccion alfa',
        ]);
        $proveedorZeta = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999102',
            'razon_social' => 'Zeta proveedor',
            'direccion' => 'Direccion zeta',
        ]);

        $manoObraAlfa = ManoObra::create([
            'semana' => 1,
            'fecha_inicio' => '2025-03-03',
            'fecha_fin' => '2025-03-09',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
        ]);
        $manoObraZeta = ManoObra::create([
            'semana' => 2,
            'fecha_inicio' => '2025-03-10',
            'fecha_fin' => '2025-03-16',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
        ]);

        DetalleManoObra::create([
            'fecha' => '2025-03-03',
            'mano_obra_id' => $manoObraAlfa->id,
            'proveedor_id' => $proveedorAlfa->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 10,
        ]);
        DetalleManoObra::create([
            'fecha' => '2025-03-10',
            'mano_obra_id' => $manoObraZeta->id,
            'proveedor_id' => $proveedorZeta->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 10,
        ]);

        $this->contexto = compact('proyecto', 'proveedorAlfa', 'proveedorZeta', 'manoObraAlfa', 'manoObraZeta');
    }

    private function idsFiltrados(array $filtros): array
    {
        $request = new Request();
        $request->merge($filtros);

        return ManoObra::filtroManoObra($request)
            ->map(fn ($item) => $item['mano_obra']->id)
            ->all();
    }

    public function test_el_filtro_proveedor_usa_el_proveedor_del_detalle(): void
    {
        $this->crearContexto();

        $ids = $this->idsFiltrados(['proveedor' => $this->contexto['proveedorAlfa']->id]);

        $this->assertSame([$this->contexto['manoObraAlfa']->id], $ids);
    }

    public function test_el_filtro_de_fechas_selecciona_los_periodos_que_solapan_el_rango(): void
    {
        $this->crearContexto();

        // Periodos: 03-03..03-09 y 03-10..03-16. El rango 03-08..03-12 no
        // contiene ninguno de los dos, pero solapa con ambos.
        $ids = $this->idsFiltrados(['fechas' => '03/08/2025 - 03/12/2025']);

        $this->assertEqualsCanonicalizing(
            [$this->contexto['manoObraAlfa']->id, $this->contexto['manoObraZeta']->id],
            $ids
        );

        // Un rango anterior a los dos periodos no trae nada.
        $this->assertSame([], $this->idsFiltrados(['fechas' => '03/01/2025 - 03/02/2025']));
    }

    public function test_el_filtro_proveedor_del_reporte_global_usa_el_detalle(): void
    {
        $this->crearContexto();

        $request = new Request();
        $request->merge([
            'proyecto' => $this->contexto['proyecto']->id,
            'proveedor' => $this->contexto['proveedorAlfa']->id,
        ]);

        // Antes fallaba con "Unknown column 'mano_obra.proveedor_id'"; sin pagos
        // registrados la seccion de mano de obra queda vacia, pero la consulta
        // tiene que resolverse contra detalle_mano_obra.
        $resultado = Adquisicion::reporteGlobalAdquisiciones($request);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('data', $resultado);
        $this->assertIsArray($resultado['data']);
    }
}
