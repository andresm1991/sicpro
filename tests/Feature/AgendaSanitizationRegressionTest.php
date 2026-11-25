<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\ComentarioTarea;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Spec: odd/tasks/permiso-roles-y-summernote-agenda.md, S1, S5, S6.
 *
 * Regresiones detectadas por la verificacion independiente: el comentario es
 * texto plano y no debe salir escapado dos veces, el PDF de la agenda no debe
 * mostrar etiquetas crudas, un href ofuscado no sobrevive, las filas viejas se
 * sanitizan al renderizar, la ruta de roles no vuelve a usar middleware de rol y
 * el espejo public/js del build tiene que estar sincronizado.
 */
class AgendaSanitizationRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
        $this->seedAgendaCatalog();
    }

    private function seedAgendaCatalog(): void
    {
        $estados = CatalogoDato::create(['descripcion' => 'Estados', 'detalle' => '', 'slug' => 'estados.tarea', 'padre_id' => null, 'activo' => true]);
        foreach (['porhacer' => 'Por Hacer', 'encurso' => 'En Curso', 'finalizado' => 'Finalizado'] as $slug => $nombre) {
            CatalogoDato::create(['descripcion' => $nombre, 'detalle' => '', 'slug' => 'estados.tarea.' . $slug, 'padre_id' => $estados->id, 'activo' => true]);
        }

        $categorias = CatalogoDato::create(['descripcion' => 'Categorias', 'detalle' => '', 'slug' => 'categorias.agenda', 'padre_id' => null, 'activo' => true]);
        foreach (['laboral' => 'Laboral', 'personal' => 'Personal', 'compartida' => 'Compartida'] as $slug => $nombre) {
            CatalogoDato::create(['descripcion' => $nombre, 'detalle' => '', 'slug' => 'categoria.agenda.' . $slug, 'padre_id' => $categorias->id, 'activo' => true]);
        }
    }

    private function admin(string $usuario): User
    {
        $user = User::factory()->create(['usuario' => $usuario, 'correo' => $usuario . '@test.com']);
        $user->assignRole('Administrador');

        return $user;
    }

    private function tarea(User $user, string $descripcion): Tarea
    {
        return Tarea::create([
            'usuario_id' => $user->id,
            'titulo' => 'Tarea ' . uniqid(),
            'descripcion' => $descripcion,
            'estado_id' => CatalogoDato::getIdCatalogo('estados.tarea.porhacer'),
            'categoria_id' => CatalogoDato::getIdCatalogo('categoria.agenda.laboral'),
        ]);
    }

    private function ajax(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest'];
    }

    /** @test */
    public function s5_el_comentario_de_texto_plano_vuelve_tal_cual_se_escribio()
    {
        $user = $this->admin('reg_comment');
        $tarea = $this->tarea($user, '<p>base</p>');
        $texto = 'Revisar A & B y 5 < 10';

        $this->actingAs($user)->post('agenda/guardar-comentario', [
            'tarea_id' => $tarea->id,
            'titulo' => $tarea->titulo,
            'descripcion' => '<p>base</p>',
            'comentario' => $texto,
            'list_usuarios' => [],
            'categoria_tarea' => CatalogoDato::getIdCatalogo('categoria.agenda.laboral'),
        ], $this->ajax())->assertOk();

        // El textarea #comentario es texto plano: el valor no debe volver con
        // &amp; / &lt;, porque la UI lo vuelve a escapar al mostrarlo.
        $this->assertSame($texto, ComentarioTarea::latest('id')->firstOrFail()->comentario);

        $this->actingAs($user)->post('agenda/guardar-comentario', [
            'tarea_id' => $tarea->id,
            'titulo' => $tarea->titulo,
            'descripcion' => '<p>base</p>',
            'comentario' => '<script>alert(1)</script>hola',
            'list_usuarios' => [],
            'categoria_tarea' => CatalogoDato::getIdCatalogo('categoria.agenda.laboral'),
        ], $this->ajax())->assertOk();

        $this->assertStringNotContainsString('<script', ComentarioTarea::latest('id')->firstOrFail()->comentario);
        $this->assertStringContainsString('hola', ComentarioTarea::latest('id')->firstOrFail()->comentario);
    }

    /** @test */
    public function s5_la_exportacion_de_la_agenda_no_muestra_etiquetas_crudas()
    {
        $user = $this->admin('reg_pdf');
        $tarea = $this->tarea($user, \App\Support\HtmlSanitizer::clean('<p>hola <strong>mundo</strong></p>'));

        $html = view('pdf.tareas', ['infoTarea' => collect(['Por Hacer' => collect([$tarea])])])->render();

        $this->assertStringNotContainsString('&lt;p&gt;', $html);
        $this->assertStringContainsString('hola mundo', $html);
    }

    /** @test */
    public function s5_un_href_ofuscado_no_sobrevive_como_javascript()
    {
        $user = $this->admin('reg_href');

        $this->actingAs($user)->post('agenda/guardar-tarea', [
            'titulo' => 'href ofuscado',
            'descripcion' => '<a href="java&#9;script:alert(1)">a</a><a href="java&#10;script:alert(2)">b</a>'
                . '<a href="&#x6a;&#x61;&#x76;&#x61;&#x73;&#x63;&#x72;&#x69;&#x70;&#x74;&#x3a;alert(3)">c</a>',
        ], $this->ajax())->assertOk();

        $stored = Tarea::latest('id')->firstOrFail()->descripcion;

        $this->assertStringNotContainsStringIgnoringCase('javascript:', $stored);
        $this->assertStringNotContainsStringIgnoringCase('onclick', $stored);
    }

    /** @test */
    public function s5_las_filas_anteriores_al_editor_se_sanitizan_al_renderizar()
    {
        $user = $this->admin('reg_legacy');
        $this->tarea(
            $user,
            '<p>antes</p><script>window.pwn=1</script><img src=x onerror="window.pwn=2"><a href="javascript:window.pwn=3">x</a>'
        );

        $html = $this->actingAs($user)->get(route('tarea.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('window.pwn', $html);
        $this->assertStringContainsString('data-descripcion="&lt;p&gt;antes&lt;/p&gt;', $html);
    }

    /** @test */
    public function s1_las_rutas_de_roles_no_usan_middleware_de_rol()
    {
        foreach (['sistema.roles.index', 'sistema.roles.edit', 'sistema.roles.update'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();

            $this->assertContains('permission:sistema.roles.ver', $middleware, $name);

            foreach ($middleware as $m) {
                $this->assertStringNotContainsString('role:', (string) $m, $name . ' no debe volver a role:');
            }
        }

        $this->assertContains(
            'permission:sistema.roles.gestionar',
            Route::getRoutes()->getByName('sistema.roles.update')->gatherMiddleware()
        );
    }

    /** @test */
    public function s6_el_espejo_publico_del_js_esta_actualizado()
    {
        $this->assertSame(
            file_get_contents(base_path('resources/js/app.js')),
            file_get_contents(base_path('public/js/app.js'))
        );
        $this->assertSame(
            file_get_contents(base_path('resources/js/tareas_scripts.js')),
            file_get_contents(base_path('public/js/tareas_scripts.js'))
        );

        $app = file_get_contents(base_path('resources/js/app.js'));
        $this->assertStringContainsString('SUMMERNOTE_DEFAULT_HEIGHT = 900', $app);
        $this->assertStringContainsString('Escribe el contenido del contrato aquí...', $app);

        $acta = file_get_contents(base_path('resources/views/marketing/seguimiento_ventas/partials/form_acta_entrega.blade.php'));
        $this->assertStringContainsString("'class' => 'form-control summernote', 'id' => 'contenido'", $acta);
        $this->assertStringNotContainsString('data-height', $acta);
    }
}
