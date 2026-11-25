<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\ComentarioTarea;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Spec: odd/tasks/permiso-roles-y-summernote-agenda.md, T2, S4-S5.
 *
 * S5: la descripcion de la tarea y la de sus comentarios se sanitizan con
 * allowlist antes de persistir (<script>, on* y javascript: se eliminan;
 * p/strong/ul/li/a[href]... se conservan) y se muestran formateadas.
 */
class AgendaHtmlSanitizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
        $this->seedAgendaCatalog();
    }

    /**
     * CatalogoDatoTableSeeder trae comentadas las secciones de agenda
     * (estados.tarea, categorias.agenda), asi que se crean aqui los nodos
     * minimos que el controlador y las vistas necesitan.
     */
    private function seedAgendaCatalog(): void
    {
        $estados = CatalogoDato::create([
            'descripcion' => 'Estados Tarea',
            'detalle' => '',
            'slug' => 'estados.tarea',
            'padre_id' => null,
            'activo' => true,
        ]);

        foreach (['porhacer' => 'Por Hacer', 'encurso' => 'En Curso', 'finalizado' => 'Finalizado'] as $slug => $nombre) {
            CatalogoDato::create([
                'descripcion' => $nombre,
                'detalle' => '',
                'slug' => 'estados.tarea.' . $slug,
                'padre_id' => $estados->id,
                'activo' => true,
            ]);
        }

        $categorias = CatalogoDato::create([
            'descripcion' => 'Categorias Agenda',
            'detalle' => '',
            'slug' => 'categorias.agenda',
            'padre_id' => null,
            'activo' => true,
        ]);

        foreach (['laboral' => 'Laboral', 'personal' => 'Personal', 'compartida' => 'Compartida'] as $slug => $nombre) {
            CatalogoDato::create([
                'descripcion' => $nombre,
                'detalle' => '',
                'slug' => 'categoria.agenda.' . $slug,
                'padre_id' => $categorias->id,
                'activo' => true,
            ]);
        }
    }

    private function adminUser(string $usuario): User
    {
        $user = User::factory()->create(['usuario' => $usuario, 'correo' => $usuario . '@test.com']);
        $user->assignRole(Role::findByName('Administrador'));

        return $user;
    }

    private function ajaxHeaders(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest'];
    }

    /** @test */
    public function guardar_tarea_sanitiza_la_descripcion_antes_de_persistir()
    {
        $user = $this->adminUser('agenda_san_store');

        $payload = '<p>Hola <strong>mundo</strong></p>'
            . '<script>alert(1)</script>'
            . '<ul><li>uno</li></ul>'
            . '<a href="javascript:alert(2)">clic</a>'
            . '<img src="x" onerror="alert(3)">';

        $response = $this->actingAs($user)->post(
            'agenda/guardar-tarea',
            ['titulo' => 'Tarea sanitizada', 'descripcion' => $payload],
            $this->ajaxHeaders()
        );

        $response->assertOk()->assertJson(['success' => true]);

        $tarea = Tarea::latest('id')->firstOrFail();

        $this->assertSame(
            '<p>Hola <strong>mundo</strong></p><ul><li>uno</li></ul><a>clic</a>',
            $tarea->descripcion
        );
        $this->assertStringNotContainsString('<script', $response->json('html'));
        $this->assertStringContainsString('<strong>mundo</strong>', $response->json('html'));
    }

    /** @test */
    public function guardar_comentario_y_update_comentario_sanitizan()
    {
        $user = $this->adminUser('agenda_san_coment');
        $categoriaId = CatalogoDato::getIdCatalogo('categoria.agenda.laboral');

        $this->actingAs($user)->post(
            'agenda/guardar-tarea',
            ['titulo' => 'Tarea con comentarios', 'descripcion' => '<p>Base <strong>ok</strong></p>'],
            $this->ajaxHeaders()
        )->assertOk()->assertJson(['success' => true]);

        $tarea = Tarea::latest('id')->firstOrFail();

        $response = $this->actingAs($user)->post(
            'agenda/guardar-comentario',
            [
                'tarea_id' => $tarea->id,
                'titulo' => 'Tarea con comentarios',
                'descripcion' => '<p>Editada <em>bien</em></p><script>alert(4)</script>',
                'comentario' => '<p>Nota <em>buena</em></p><script>alert(9)</script><b onmouseover="x=1">n</b>',
                'list_usuarios' => [],
                'categoria_tarea' => $categoriaId,
            ],
            $this->ajaxHeaders()
        );

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSame(
            '<p>Editada <em>bien</em></p>',
            $tarea->fresh()->descripcion
        );

        // El comentario es contenido del editor (S7): se sanitiza con la
        // misma allowlist que la descripcion.
        $this->assertSame(
            '<p>Nota <em>buena</em></p><b>n</b>',
            ComentarioTarea::latest('id')->firstOrFail()->comentario
        );

        $comentario = ComentarioTarea::latest('id')->firstOrFail();

        $this->actingAs($user)->put(
            'agenda/comentarios-tarea/' . $comentario->id,
            ['comentario' => '<a href="javascript:alert(1)">x</a><p>fin <strong>si</strong></p>'],
            $this->ajaxHeaders()
        )->assertOk()->assertJson(['success' => true]);

        $this->assertSame(
            '<a>x</a><p>fin <strong>si</strong></p>',
            $comentario->fresh()->comentario
        );
    }

    /** @test */
    public function el_href_javascript_y_data_se_rechazan_y_el_seguro_se_conserva()
    {
        $user = $this->adminUser('agenda_san_href');

        $payload = '<a href="javascript:alert(1)">mal</a> '
            . '<a href="data:text/html;base64,eA==">dat</a> '
            . '<a href="https://sicpro.test/x?a=1&b=2">bien</a>';

        $this->actingAs($user)->post(
            'agenda/guardar-tarea',
            ['titulo' => 'Tarea href', 'descripcion' => $payload],
            $this->ajaxHeaders()
        )->assertOk()->assertJson(['success' => true]);

        $this->assertSame(
            '<a>mal</a> <a>dat</a> <a href="https://sicpro.test/x?a=1&amp;b=2">bien</a>',
            Tarea::latest('id')->firstOrFail()->descripcion
        );
    }

    /** @test */
    public function la_lista_muestra_el_html_almacenado_formateado()
    {
        $user = $this->adminUser('agenda_san_render');

        $this->actingAs($user)->post(
            'agenda/guardar-tarea',
            ['titulo' => 'Tarea visible', 'descripcion' => '<p>Texto <strong>negrita</strong></p>'],
            $this->ajaxHeaders()
        )->assertOk()->assertJson(['success' => true]);

        $tarea = Tarea::latest('id')->firstOrFail();

        $response = $this->actingAs($user)->get(route('tarea.index'));

        $response->assertOk();
        // El detalle muestra el HTML formateado, no entidades escapadas.
        $response->assertSee('<div class="text-truncate-max-line-3 mb-1"><p>Texto <strong>negrita</strong></p></div>', false);
        // El atributo que alimenta al editor JS sigue escapado.
        $response->assertSee('data-descripcion="&lt;p&gt;', false);

        $detalle = $this->actingAs($user)->get(route('tarea.visualizar', $tarea));

        $detalle->assertOk();
        $detalle->assertSee('<strong>negrita</strong>', false);
    }
}
