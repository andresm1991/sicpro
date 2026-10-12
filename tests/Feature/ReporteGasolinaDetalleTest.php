<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\AdquisicionDetalle;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Reporte de gasolina camioneta.
 *
 * El pedido puede traer varias lineas, asi que la fila del reporte tiene que
 * salir del detalle de gasolina y no de la primera linea; y con galones en
 * cero el rendimiento debe informarse sin romper el reporte.
 */
class ReporteGasolinaDetalleTest extends TestCase
{
    use RefreshDatabase;

    private function crearContexto(): array
    {
        $user = User::factory()->create(['usuario' => 'usuario_gasolina_test']);
        $etapa = CatalogoDato::create(['descripcion' => 'Etapa gasolina', 'slug' => 'etapa-gasolina', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa gasolina', 'slug' => 'tipo-etapa-gasolina', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto gasolina', 'slug' => 'catalogo-proyecto-gasolina', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto gasolina', 'slug' => 'estado-proyecto-gasolina', 'activo' => true]);
        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo gasolina', 'slug' => 'categoria-articulo-gasolina', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto gasolina',
            'nombre_propietario' => 'Propietario gasolina',
            'telefono' => '0999999999',
            'correo' => 'gasolina@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $articuloNormal = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-NORMAL',
            'descripcion' => 'Filtro de aceite',
            'activo' => true,
        ]);
        $articuloGasolina = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-GAS',
            'descripcion' => Adquisicion::PRODUCTO_GASOLINA_CAMIONETA,
            'activo' => true,
        ]);

        $adquisicion = Adquisicion::create([
            'fecha' => '2025-02-10',
            'numero' => 'ADQ-GASOLINA',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
            'estado' => 'Completado',
            'tipo_adquisicion' => 'operativo',
        ]);

        // La linea que NO es gasolina se crea primero: es la que tomaba first().
        AdquisicionDetalle::create([
            'adquisicion_id' => $adquisicion->id,
            'articulo_id' => $articuloNormal->id,
            'cantidad_solicitada' => 99,
            'iva' => 0,
            'necesidad' => 'repuesto',
            'kilometraje' => 0,
        ]);

        $detalleGasolina = AdquisicionDetalle::create([
            'adquisicion_id' => $adquisicion->id,
            'articulo_id' => $articuloGasolina->id,
            'cantidad_solicitada' => 10,
            'iva' => 0,
            'necesidad' => 'combustible',
            'kilometraje' => 100,
            'valor' => 0,
        ]);

        return compact('adquisicion', 'detalleGasolina');
    }

    public function test_toma_el_detalle_de_gasolina_y_no_la_primera_linea_del_pedido(): void
    {
        $this->crearContexto();

        $resultado = Adquisicion::reporteGasolinaCamioneta(new Request());

        $this->assertCount(1, $resultado);
        $fila = $resultado->first();
        $this->assertSame(10.0, $fila['galones']);
        $this->assertSame(100.0, $fila['km_carga']);
    }

    public function test_con_galones_en_cero_informa_rendimiento_cero_sin_romper_el_reporte(): void
    {
        ['detalleGasolina' => $detalleGasolina] = $this->crearContexto();
        $detalleGasolina->update(['cantidad_solicitada' => 0]);

        $resultado = Adquisicion::reporteGasolinaCamioneta(new Request());

        $this->assertCount(1, $resultado);
        $this->assertSame(0, $resultado->first()['km_galon']);
    }
}
