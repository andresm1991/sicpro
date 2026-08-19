<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\DetalleManoObra;
use App\Models\ManoObra;
use App\Models\PagoManoObra;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use App\Http\Controllers\ManoObraController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManoObraDestroyTest extends TestCase
{
    use RefreshDatabase;

    private function crearContextoBase(): array
    {
        $user = User::factory()->create(['usuario' => 'usuario_destroy_test']);
        $etapa = CatalogoDato::create(['descripcion' => 'Etapa destroy test', 'slug' => 'etapa-destroy-test', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa destroy test', 'slug' => 'tipo-etapa-destroy-test', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto destroy test', 'slug' => 'catalogo-proyecto-destroy-test', 'activo' => true]);
        $estado = CatalogoDato::create(['descripcion' => 'Estado proyecto destroy test', 'slug' => 'estado-proyecto-destroy-test', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto destroy test',
            'nombre_propietario' => 'Propietario destroy test',
            'telefono' => '0999999999',
            'correo' => 'destroy-test@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estado->id,
        ]);

        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo destroy test', 'slug' => 'categoria-articulo-destroy-test', 'activo' => true]);
        $articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-DT',
            'descripcion' => 'Oficial',
            'activo' => true,
        ]);

        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor destroy test', 'slug' => 'categoria-proveedor-destroy-test', 'activo' => true]);
        $proveedor = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999001',
            'razon_social' => 'Trabajador destroy test',
            'direccion' => 'Direccion destroy test',
        ]);

        $manoObra = ManoObra::create([
            'semana' => 1,
            'fecha_inicio' => '2026-08-10',
            'fecha_fin' => '2026-08-16',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
        ]);

        return compact('manoObra', 'proveedor', 'articulo');
    }

    private function peticionDestroy(int $manoObraId): \Illuminate\Http\JsonResponse
    {
        $request = new Request();
        $request->merge(['mano_obra' => $manoObraId]);

        return app(ManoObraController::class)->destroy($request);
    }

    public function test_destroy_returns_error_json_when_planificacion_does_not_exist()
    {
        $response = $this->peticionDestroy(99999);

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('No se pudo encontrar el registro', $data['message']);
        $this->assertDatabaseMissing('mano_obra', ['id' => 99999]);
    }

    public function test_destroy_blocks_deletion_when_planificacion_is_paid()
    {
        ['manoObra' => $manoObra] = $this->crearContextoBase();

        // Insert a payment row without building the full loan chain.
        Schema::disableForeignKeyConstraints();
        PagoManoObra::create(['mano_obra_id' => $manoObra->id, 'pago_prestamo_id' => 1]);
        Schema::enableForeignKeyConstraints();

        $response = $this->peticionDestroy($manoObra->id);

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('ya se encuentra pagada', $data['message']);
        $this->assertDatabaseHas('mano_obra', ['id' => $manoObra->id]);
        $this->assertDatabaseHas('pagos_mano_obra', ['mano_obra_id' => $manoObra->id]);
    }

    public function test_destroy_deletes_details_and_planificacion_when_not_paid()
    {
        ['manoObra' => $manoObra, 'proveedor' => $proveedor, 'articulo' => $articulo] = $this->crearContextoBase();

        DetalleManoObra::create([
            'fecha' => '2026-08-10',
            'mano_obra_id' => $manoObra->id,
            'proveedor_id' => $proveedor->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 25.5,
        ]);

        $response = $this->peticionDestroy($manoObra->id);

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertDatabaseMissing('mano_obra', ['id' => $manoObra->id]);
        $this->assertDatabaseMissing('detalle_mano_obra', ['mano_obra_id' => $manoObra->id]);
    }
}
