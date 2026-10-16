<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Auditoria del registro publico (T12): registrarse crea una credencial y
 * debe dejar una fila en `logs` con entidad=user/accion=crear, sin guardar
 * nunca la clave ni su hash.
 */
class AuditoriaRegistroTest extends TestCase
{
    use RefreshDatabase;

    private function payloadRegistro(string $email = 'registro_publico@example.com', string $clave = 'clave-secreta-123'): array
    {
        return [
            'name' => 'Registro Publico',
            'email' => $email,
            'password' => $clave,
            'password_confirmation' => $clave,
        ];
    }

    public function test_registro_deja_auditoria_con_datos_no_sensibles(): void
    {
        $response = $this->post(route('register'), $this->payloadRegistro());

        $response->assertRedirect('/home');

        $nuevo = User::where('correo', 'registro_publico@example.com')->first();
        $this->assertNotNull($nuevo);

        $log = Log::where('entidad', 'user')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($nuevo->id, $log->entidad_id);
        $this->assertSame($nuevo->usuario, $log->valores_despues['usuario']);
        $this->assertSame($nuevo->nombre, $log->valores_despues['nombre']);
        $this->assertSame($nuevo->correo, $log->valores_despues['correo']);
        $this->assertArrayHasKey('activo', $log->valores_despues);
        $this->assertStringContainsString($nuevo->usuario, $log->message);
    }

    public function test_registro_no_guarda_clave_ni_hash(): void
    {
        $clave = 'clave-super-secreta-456';

        $this->post(route('register'), $this->payloadRegistro('sin_hash@example.com', $clave));

        $nuevo = User::where('correo', 'sin_hash@example.com')->first();
        $this->assertNotNull($nuevo);
        $this->assertTrue(Hash::check($clave, $nuevo->clave));

        $log = Log::where('entidad', 'user')->where('accion', 'crear')->latest('id')->first();
        $this->assertNotNull($log);

        $serializado = json_encode([
            $log->message,
            $log->valores_antes,
            $log->valores_despues,
        ]);
        $this->assertStringNotContainsString($clave, $serializado);
        $this->assertStringNotContainsString($nuevo->clave, $serializado);
        $this->assertArrayNotHasKey('clave', $log->valores_despues ?? []);
        $this->assertArrayNotHasKey('password', $log->valores_despues ?? []);
        $this->assertArrayNotHasKey('clave', $log->valores_antes ?? []);
        $this->assertArrayNotHasKey('password', $log->valores_antes ?? []);
    }

    public function test_registro_mantiene_flujo_igual(): void
    {
        $response = $this->post(route('register'), $this->payloadRegistro('flujo_igual@example.com'));

        $response->assertRedirect('/home');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('usuarios', ['correo' => 'flujo_igual@example.com']);
    }
}
