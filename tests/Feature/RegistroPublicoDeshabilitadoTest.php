<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El registro publico esta apagado: el sistema es interno y con roles, asi que
 * las cuentas las crea un administrador desde Sistema > Usuarios.
 *
 * Antes de apagarlo el POST /register devolvia 500 (validador contra la tabla
 * inexistente `users`); ahora la ruta directamente no existe.
 */
class RegistroPublicoDeshabilitadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_formulario_de_registro_no_existe(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_no_se_puede_crear_un_usuario_por_el_registro_publico(): void
    {
        $antes = User::count();

        $this->post('/register', [
            'name' => 'Cuenta publica',
            'email' => 'publica@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertNotFound();

        $this->assertSame($antes, User::count(), 'El registro publico no puede crear usuarios.');
    }

    public function test_el_login_y_el_resto_de_las_rutas_de_autenticacion_siguen_funcionando(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/password/reset')->assertOk();
    }
}
