<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Filtro de subproyectos del reporte de adquisiciones.
 *
 * El JS manda los dos ids en el path (.../subproyectos/{proyecto}/{tipo}) pero
 * el controlador leia $request->input('tipo'), que no ve los parametros de
 * ruta: la rama por tipo nunca corria y el desplegable lista siempre todos los
 * subproyectos del proyecto. Adentro de esa rama, el caso contratista filtraba
 * por proveedor_id, que nunca coincide con un id de proyecto.
 */
class FiltroSubProyectosTest extends TestCase
{
    use RefreshDatabase;

    private function url($proyecto, $tipo): string
    {
        return "/reportes/filtro-reporte-adquisiciones/subproyectos/{$proyecto}/{$tipo}";
    }

    private function usuario(): User
    {
        return User::factory()->create([
            'usuario' => 'usuario_filtro_subproyectos_test',
            'correo' => 'usuario_filtro_subproyectos_test@example.test',
        ]);
    }

    private function catalogo(string $slug): CatalogoDato
    {
        return CatalogoDato::create([
            'descripcion' => $slug,
            'detalle' => '',
            'slug' => $slug,
            'padre_id' => null,
            'activo' => true,
        ]);
    }

    private function proyecto(CatalogoDato $catalogo, string $nombre): int
    {
        return DB::table('proyectos')->insertGetId([
            'catalogo_proyecto_id' => $catalogo->id,
            'nombre_proyecto' => $nombre,
            'nombre_propietario' => 'Propietario de prueba',
            'telefono' => '0991000000',
            'correo' => 'proyecto@example.test',
            'tipo_proyecto_id' => $catalogo->id,
            'estado_id' => $catalogo->id,
            'portada' => '',
        ]);
    }

    /**
     * Un contratista del proyecto $proyectoId con subproyecto propio.
     */
    private function contratista(CatalogoDato $catalogo, int $proyectoId, User $usuario, string $subproyecto): void
    {
        $proveedorId = DB::table('proveedores')->insertGetId([
            'categoria_proveedor_id' => $catalogo->id,
            'documento' => '80012345',
            'razon_social' => 'Proveedor de prueba',
            'direccion' => 'Calle de prueba',
        ]);

        $articuloId = DB::table('articulos')->insertGetId([
            'categoria_id' => $catalogo->id,
            'descripcion' => 'Articulo de prueba',
            'activo' => true,
        ]);

        DB::table('contratistas')->insert([
            'fecha' => '2025-03-10',
            'plazo_semanas' => 4,
            'proveedor_id' => $proveedorId,
            'articulo_id' => $articuloId,
            'proyecto_id' => $proyectoId,
            'etapa_id' => $catalogo->id,
            'tipo_etapa_id' => $catalogo->id,
            'usuario_id' => $usuario->id,
            'estado_id' => $catalogo->id,
            'subproyecto' => $subproyecto,
        ]);
    }

    /**
     * Escenario: un tipo "contratista" valido y un contratista con subproyecto
     * propio en el proyecto 2 (el proveedor del contratista es el id 1, asi que
     * filtrar por proveedor_id daria vacio).
     *
     * @return array{0: CatalogoDato, 1: int, 2: User, 3: int}
     */
    private function escenario(): array
    {
        $usuario = $this->usuario();
        $tipoContratista = $this->catalogo('contratista');
        $this->catalogo('mano.obra');
        $proyectoUno = $this->proyecto($tipoContratista, 'Proyecto uno');
        $proyectoDos = $this->proyecto($tipoContratista, 'Proyecto dos');
        $this->contratista($tipoContratista, $proyectoDos, $usuario, 'casa 5');

        return [$tipoContratista, $proyectoDos, $usuario, $proyectoUno];
    }

    private function pedir(int $proyecto, $tipo, User $usuario)
    {
        return $this->actingAs($usuario)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get($this->url($proyecto, $tipo));
    }

    public function test_un_tipo_inexistente_responde_con_mensaje_claro(): void
    {
        [, $proyecto, $usuario] = $this->escenario();

        $response = $this->pedir($proyecto, 999999, $usuario);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertNotEmpty($response->json('mensaje'));
    }

    public function test_un_proyecto_inexistente_no_revienta(): void
    {
        [, , $usuario] = $this->escenario();

        $response = $this->pedir(999999, 0, $usuario);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame(['' => ''], (array) $response->json('subproyectos'));
    }

    public function test_el_filtro_por_tipo_acota_los_subproyectos_del_proyecto(): void
    {
        [, $proyecto, $usuario] = $this->escenario();
        $tipoManoObra = CatalogoDato::where('slug', 'mano.obra')->first();

        $response = $this->pedir($proyecto, $tipoManoObra->id, $usuario);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertArrayNotHasKey(
            'casa 5',
            (array) $response->json('subproyectos'),
            'Filtrando por mano de obra no puede aparecer el subproyecto de un contratista.'
        );
    }

    public function test_sin_tipo_elegido_sigue_devolviendo_la_lista_del_proyecto(): void
    {
        [, $proyecto, $usuario] = $this->escenario();

        $response = $this->pedir($proyecto, 0, $usuario);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertArrayHasKey('casa 5', (array) $response->json('subproyectos'));
    }

    public function test_el_tipo_contratista_lista_los_subproyectos_del_proyecto_pedido(): void
    {
        [$tipoContratista, $proyecto, $usuario] = $this->escenario();

        $response = $this->pedir($proyecto, $tipoContratista->id, $usuario);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertArrayHasKey(
            'casa 5',
            (array) $response->json('subproyectos'),
            'El contratista se busca por proyecto_id: con proveedor_id (id 1) la lista saldria vacia.'
        );
    }
}
