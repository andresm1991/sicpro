<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Contratista;
use App\Models\DetalleManoObra;
use App\Models\ManoObra;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Ordenamiento de los reportes de contratistas y mano de obra.
 *
 * mano_obra no tiene columna proveedor_id (el proveedor vive en
 * detalle_mano_obra) ni columna fecha (usa fecha_inicio/fecha_fin), asi que
 * ordenar por esas columnas rompia la consulta.
 */
class OrdenReportesProveedorTest extends TestCase
{
    use RefreshDatabase;

    private array $contexto = [];

    private function crearContexto(): void
    {
        $user = User::factory()->create(['usuario' => 'usuario_orden_test']);
        $etapa = CatalogoDato::create(['descripcion' => 'Etapa orden', 'slug' => 'etapa-orden', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa orden', 'slug' => 'tipo-etapa-orden', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto orden', 'slug' => 'catalogo-proyecto-orden', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto orden', 'slug' => 'estado-proyecto-orden', 'activo' => true]);
        $estadoContratista = CatalogoDato::create(['descripcion' => 'Estado contratista orden', 'slug' => 'estado-contratista-orden', 'activo' => true]);
        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo orden', 'slug' => 'categoria-articulo-orden', 'activo' => true]);
        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor orden', 'slug' => 'categoria-proveedor-orden', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto orden',
            'nombre_propietario' => 'Propietario orden',
            'telefono' => '0999999999',
            'correo' => 'orden@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-ORDEN',
            'descripcion' => 'Oficial',
            'activo' => true,
        ]);

        $proveedorZeta = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999001',
            'razon_social' => 'Zeta proveedor',
            'direccion' => 'Direccion zeta',
        ]);
        $proveedorAlfa = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999002',
            'razon_social' => 'Alfa proveedor',
            'direccion' => 'Direccion alfa',
        ]);

        $contratistaZeta = Contratista::create([
            'fecha' => '2025-03-01',
            'plazo_semanas' => 4,
            'proveedor_id' => $proveedorZeta->id,
            'articulo_id' => $articulo->id,
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
            'estado_id' => $estadoContratista->id,
        ]);
        $contratistaAlfa = Contratista::create([
            'fecha' => '2025-03-02',
            'plazo_semanas' => 4,
            'proveedor_id' => $proveedorAlfa->id,
            'articulo_id' => $articulo->id,
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
            'estado_id' => $estadoContratista->id,
        ]);

        $manoObraZeta = ManoObra::create([
            'semana' => 1,
            'fecha_inicio' => '2025-03-03',
            'fecha_fin' => '2025-03-09',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
        ]);
        $manoObraAlfa = ManoObra::create([
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
            'mano_obra_id' => $manoObraZeta->id,
            'proveedor_id' => $proveedorZeta->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 10,
        ]);
        DetalleManoObra::create([
            'fecha' => '2025-03-10',
            'mano_obra_id' => $manoObraAlfa->id,
            'proveedor_id' => $proveedorAlfa->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 10,
        ]);

        $this->contexto = compact('contratistaZeta', 'contratistaAlfa', 'manoObraZeta', 'manoObraAlfa');
    }

    public function test_contratistas_alfabetico_ordena_por_razon_social_del_proveedor(): void
    {
        $this->crearContexto();

        $request = new Request();
        $request->merge(['ordenado' => 'alfabetico']);

        $ids = Contratista::filtroContratista($request)
            ->map(fn ($item) => $item['contratista']->id)
            ->all();

        $this->assertSame(
            [$this->contexto['contratistaAlfa']->id, $this->contexto['contratistaZeta']->id],
            $ids
        );
    }

    public function test_mano_de_obra_alfabetico_ordena_por_el_proveedor_del_detalle(): void
    {
        $this->crearContexto();

        $request = new Request();
        $request->merge(['ordenado' => 'alfabetico']);

        $ids = ManoObra::filtroManoObra($request)
            ->map(fn ($item) => $item['mano_obra']->id)
            ->all();

        $this->assertSame(
            [$this->contexto['manoObraAlfa']->id, $this->contexto['manoObraZeta']->id],
            $ids
        );
    }

    public function test_mano_de_obra_fecha_ordena_por_fecha_inicio(): void
    {
        $this->crearContexto();

        $request = new Request();
        $request->merge(['ordenado' => 'fecha']);

        $ids = ManoObra::filtroManoObra($request)
            ->map(fn ($item) => $item['mano_obra']->id)
            ->all();

        $this->assertSame(
            [$this->contexto['manoObraZeta']->id, $this->contexto['manoObraAlfa']->id],
            $ids
        );
    }
}
