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
    public function s7_el_comentario_rico_conserva_formato_y_sanitiza()
    {
        $user = $this->admin('reg_comment');
        $tarea = $this->tarea($user, '<p>base</p>');
        $texto = 'A & B y 5 < 10';

        $this->actingAs($user)->post('agenda/guardar-comentario', [
            'tarea_id' => $tarea->id,
            'titulo' => $tarea->titulo,
            'descripcion' => '<p>base</p>',
            'comentario' => $texto,
            'list_usuarios' => [],
            'categoria_tarea' => CatalogoDato::getIdCatalogo('categoria.agenda.laboral'),
        ], $this->ajax())->assertOk();

        // El comentario es HTML (S7): el sanitizador serializa el texto plano
        // una sola vez (& -> &amp;, < -> &lt;). Al interpolarlo en crudo en
        // el div se ve "A & B y 5 < 10" sin doble-encoding (&amp;amp;).
        // No se puede esperar igualdad literal porque la columna guarda HTML.
        $stored = ComentarioTarea::latest('id')->firstOrFail()->comentario;
        $this->assertSame('A &amp; B y 5 &lt; 10', $stored);
        $this->assertStringNotContainsString('&amp;amp;', $stored);
        $this->assertSame($texto, html_entity_decode($stored, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $this->actingAs($user)->post('agenda/guardar-comentario', [
            'tarea_id' => $tarea->id,
            'titulo' => $tarea->titulo,
            'descripcion' => '<p>base</p>',
            'comentario' => '<p>Hola <strong>x</strong></p><script>alert(1)</script><img src=x onerror="alert(2)">',
            'list_usuarios' => [],
            'categoria_tarea' => CatalogoDato::getIdCatalogo('categoria.agenda.laboral'),
        ], $this->ajax())->assertOk();

        $storedHtml = ComentarioTarea::latest('id')->firstOrFail()->comentario;
        $this->assertStringContainsString('<strong>x</strong>', $storedHtml);
        $this->assertStringNotContainsString('<script', $storedHtml);
        $this->assertStringNotContainsStringIgnoringCase('onerror', $storedHtml);
    }

    /** @test */
    public function s7_get_comentarios_tarea_sanitiza_filas_anteriores_al_editor()
    {
        $user = $this->admin('reg_legacy_comment');
        $tarea = $this->tarea($user, '<p>base</p>');

        // Fila guardada antes del editor: nunca paso por el sanitizador.
        $legacy = ComentarioTarea::create([
            'tarea_id' => $tarea->id,
            'usuario_id' => $user->id,
            'comentario' => '<script>alert(1)</script><p>legado <strong>ok</strong></p>',
        ]);

        $response = $this->actingAs($user)->get(
            'agenda/comentarios-tarea?tarea_id=' . $tarea->id,
            $this->ajax()
        );

        $response->assertOk()->assertJson(['success' => true]);

        $payload = collect($response->json('comentarios'))->firstWhere('id', $legacy->id);
        $this->assertNotNull($payload);
        $this->assertStringNotContainsString('<script', $payload['comentario']);
        $this->assertStringContainsString('<strong>ok</strong>', $payload['comentario']);
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
        $this->assertDoesNotMatchRegularExpression('#href\s*=#i', $stored);

        // Control positivo: la allowlist no es vacuamente estricta.
        $this->actingAs($user)->post('agenda/guardar-tarea', [
            'titulo' => 'href permitido',
            'descripcion' => '<a href="https://sicpro.test/x?a=1&amp;b=2">bien</a>'
                . '<a href="/ruta/relativa">rel</a><a href="#ancla">anc</a>',
        ], $this->ajax())->assertOk();

        $storedOk = Tarea::latest('id')->firstOrFail()->descripcion;

        $this->assertStringNotContainsStringIgnoringCase('javascript:', $storedOk);
        $this->assertMatchesRegularExpression('/href\s*=\s*"https:\/\/sicpro\.test\/x\?a=1&amp;b=2"/i', $storedOk);
        $this->assertMatchesRegularExpression('/href\s*=\s*"\/ruta\/relativa"/i', $storedOk);
        $this->assertMatchesRegularExpression('/href\s*=\s*"#ancla"/i', $storedOk);
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

        // Barra minima comun a todos los editores: nada despues de 'table'.
        $start = strpos($app, 'SUMMERNOTE_DEFAULT_TOOLBAR = [');
        $this->assertNotFalse($start, 'no se encontro la toolbar por defecto');
        $toolbar = substr($app, $start, strpos($app, '];', $start) - $start);

        $this->assertStringContainsString("['table', ['table']]", $toolbar);
        $this->assertStringNotContainsString('codeview', $toolbar);
        $this->assertStringNotContainsString('picture', $toolbar);
        $this->assertStringNotContainsString('video', $toolbar);
        $this->assertStringNotContainsString("'link'", $toolbar);
        $this->assertStringNotContainsString('fullscreen', $toolbar);

        $acta = file_get_contents(base_path('resources/views/marketing/seguimiento_ventas/partials/form_acta_entrega.blade.php'));
        $this->assertStringContainsString("'class' => 'form-control summernote', 'id' => 'contenido'", $acta);
        $this->assertStringNotContainsString('data-height', $acta);
    }

    /** @test */
    public function s7_la_edicion_inline_devuelve_el_comentario_sanitizado()
    {
        $user = $this->admin('reg_inline_xss');
        $tarea = $this->tarea($user, '<p>base</p>');

        $comentario = ComentarioTarea::create([
            'tarea_id' => $tarea->id,
            'usuario_id' => $user->id,
            'comentario' => '<p>original</p>',
        ]);

        $response = $this->actingAs($user)->put(
            'agenda/comentarios-tarea/' . $comentario->id,
            ['comentario' => '<p>ok <strong>si</strong></p><script>alert(1)</script><img src=x onerror="alert(2)">'],
            $this->ajax()
        );

        $response->assertOk()->assertJson(['success' => true]);

        // La UI renderiza la respuesta: tiene que venir ya sanitizada, no el
        // HTML que armo el editor en el navegador.
        $devuelto = $response->json('comentario');

        $this->assertIsString($devuelto);
        $this->assertStringNotContainsString('<script', $devuelto);
        $this->assertStringNotContainsString('onerror', $devuelto);
        $this->assertStringContainsString('<strong>si</strong>', $devuelto);
        $this->assertSame($comentario->fresh()->comentario, $devuelto);
    }
}
