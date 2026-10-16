<?php

namespace Tests\Feature;

use App\Models\JPLimpieza\Log as JpLog;
use App\Models\User;
use App\Services\JPLimpieza\LogService as JpLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Auditoria de JP Limpieza en su propia base (S4: "Una tabla por base").
 *
 * La conexion mysql_jp_limpieza se aisla con DB_JPLIMPIEZA_DATABASE en
 * phpunit.xml. Sin eso apunta a la base real jp_limpieza y este test escribiria
 * en datos de produccion: se verifica en vez de confiar. Si salta, esta base
 * no esta aislada.
 *
 * La tabla `logs` de jp_limpieza_testing NO la crea RefreshDatabase (solo
 * migra la conexion default): se aplica una vez por maquina con el DDL de
 * database/scripts/2026_10_16_create_logs_jp_limpieza.sql contra
 * jp_limpieza_testing, por ejemplo:
 *   mysql -uroot jp_limpieza_testing < database/scripts/2026_10_16_create_logs_jp_limpieza.sql
 */
class AuditoriaJPLogTest extends TestCase
{
    use RefreshDatabase;

    /** Las escrituras de JP tambien tienen que revertirse entre tests. */
    protected $connectionsToTransact = ['mysql', 'mysql_jp_limpieza'];

    protected function setUp(): void
    {
        parent::setUp();

        // Red de seguridad: escribir en la base real jp_limpieza seria un accidente
        // silencioso. El override de phpunit.xml puede quedar anulado por
        // DATABASE_JPLIMPIEZA_URL (config/database.php), asi que se verifica en vez
        // de confiar. Si salta, esta base no esta aislada.
        if (!str_ends_with((string) config('database.connections.mysql_jp_limpieza.database'), '_testing')) {
            $this->markTestSkipped(
                'mysql_jp_limpieza no apunta a una base de testing: correr este test escribiria en datos reales.'
            );
        }

        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create(['usuario' => 'admin_aud_jp', 'correo' => 'admin_aud_jp@test.com']);
        $user->assignRole(Role::findByName('Administrador'));

        return $user;
    }

    private function operativo(): User
    {
        // Operativo no tiene ningun permiso sistema.* (ver RoleTableSeeder).
        $user = User::factory()->create(['usuario' => 'operativo_aud_jp', 'correo' => 'operativo_aud_jp@test.com']);
        $user->assignRole(Role::findByName('Operativo'));

        return $user;
    }

    public function test_servicio_jp_escribe_en_la_conexion_jp_con_entidad_accion_y_actor(): void
    {
        $admin = $this->admin();

        // Peticion previa para que haya request con IP (127.0.0.1 en tests).
        $this->actingAs($admin)->get(route('sistema.index'));

        JpLogService::auditar(
            'crear',
            'adquisicion_jp',
            11,
            'Alta JP unica-jp-a',
            ['numero' => 'provisorio'],
            ['numero' => '20261016-011']
        );

        // Afirmado contra la conexion JP, no la principal.
        $fila = DB::connection('mysql_jp_limpieza')->table('logs')->where('message', 'Alta JP unica-jp-a')->first();

        $this->assertNotNull($fila, 'El servicio JP debio escribir en la conexion mysql_jp_limpieza.');
        $this->assertSame('crear', $fila->accion);
        $this->assertSame('adquisicion_jp', $fila->entidad);
        $this->assertEquals(11, $fila->entidad_id);
        $this->assertEquals($admin->id, $fila->user_id);
        $this->assertSame('127.0.0.1', $fila->ip);

        $log = JpLog::where('message', 'Alta JP unica-jp-a')->first();
        $this->assertNotNull($log);
        $this->assertSame('mysql_jp_limpieza', $log->getConnectionName());
        $this->assertSame(['user_id' => $admin->id], $log->context);
        $this->assertSame(['numero' => 'provisorio'], $log->valores_antes);
        $this->assertSame(['numero' => '20261016-011'], $log->valores_despues);

        // La fila no existe en la base principal.
        $this->assertDatabaseMissing('logs', ['message' => 'Alta JP unica-jp-a']);
    }

    public function test_log_jp_mantiene_la_forma_vieja_en_la_conexion_jp(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        // Misma firma que el servicio principal: level, message, context con user_id.
        JpLogService::log('error', 'Fallo JP unico-jp-b', ['extra' => 1]);

        $fila = DB::connection('mysql_jp_limpieza')->table('logs')->where('message', 'Fallo JP unico-jp-b')->first();

        $this->assertNotNull($fila, 'El log JP debio escribir en la conexion mysql_jp_limpieza.');
        $this->assertSame('error', $fila->level);
        $this->assertNull($fila->entidad);
        $this->assertNull($fila->accion);

        $log = JpLog::where('message', 'Fallo JP unico-jp-b')->first();
        $this->assertNotNull($log);
        $this->assertEquals(['user_id' => $admin->id, 'extra' => 1], $log->context);

        // La fila no existe en la base principal.
        $this->assertDatabaseMissing('logs', ['message' => 'Fallo JP unico-jp-b']);
    }

    public function test_visor_con_base_jp_muestra_la_fila_jp_y_con_base_principal_no(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        JpLogService::auditar('eliminar', 'adquisicion_jp', 12, 'Baja JP unica-jp-c', ['numero' => '20261016-012']);

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index', ['base' => 'jp']));

        $response->assertOk();
        $response->assertSee('Baja JP unica-jp-c');
        $response->assertSee('adquisicion_jp');
        $response->assertSee('base de JP');

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index', ['base' => 'principal']));

        $response->assertOk();
        $response->assertDontSee('Baja JP unica-jp-c');

        // Sin parametro tambien lee la principal.
        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index'));

        $response->assertOk();
        $response->assertDontSee('Baja JP unica-jp-c');
    }

    public function test_visor_con_base_invalida_vuelve_a_la_principal(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        JpLogService::auditar('crear', 'adquisicion_jp', 13, 'Alta JP unica-jp-d');

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index', ['base' => 'otra']));

        $response->assertOk();
        $response->assertDontSee('Alta JP unica-jp-d');
    }

    public function test_permiso_sigue_rigiendo_el_visor_en_ambas_bases(): void
    {
        $operativo = $this->operativo();

        $response = $this->actingAs($operativo)->get(route('sistema.auditoria.index', ['base' => 'jp']));

        $response->assertForbidden();

        $response = $this->actingAs($operativo)->get(route('sistema.auditoria.index', ['base' => 'principal']));

        $response->assertForbidden();
    }
}
