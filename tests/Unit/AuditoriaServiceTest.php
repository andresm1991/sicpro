<?php

namespace Tests\Unit;

use App\Services\LogService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Garantia S5: LogService::log() conserva su firma y comportamiento, y
 * LogService::auditar() expone exactamente la firma contratada.
 * Sin base de datos: solo reflexion sobre la clase.
 */
class AuditoriaServiceTest extends TestCase
{
    public function test_log_conserva_firma_original(): void
    {
        $metodo = new ReflectionMethod(LogService::class, 'log');

        $this->assertTrue($metodo->isStatic());

        $parametros = $metodo->getParameters();
        $this->assertCount(3, $parametros);
        $this->assertSame('level', $parametros[0]->getName());
        $this->assertSame('message', $parametros[1]->getName());
        $this->assertSame('context', $parametros[2]->getName());
        $this->assertTrue($parametros[2]->isDefaultValueAvailable());
        $this->assertSame([], $parametros[2]->getDefaultValue());
    }

    public function test_auditar_expone_la_firma_contratada(): void
    {
        $metodo = new ReflectionMethod(LogService::class, 'auditar');

        $this->assertTrue($metodo->isStatic());
        $this->assertSame('void', (string) $metodo->getReturnType());

        $parametros = $metodo->getParameters();
        $nombres = array_map(fn ($p) => $p->getName(), $parametros);

        $this->assertSame(
            ['accion', 'entidad', 'entidadId', 'mensaje', 'antes', 'despues', 'level'],
            $nombres
        );

        $this->assertSame('string', (string) $parametros[0]->getType());
        $this->assertSame('string', (string) $parametros[1]->getType());
        $this->assertTrue($parametros[2]->allowsNull());
        $this->assertSame('string', (string) $parametros[3]->getType());
        $this->assertSame('array', (string) $parametros[4]->getType());
        $this->assertSame([], $parametros[4]->getDefaultValue());
        $this->assertSame('array', (string) $parametros[5]->getType());
        $this->assertSame([], $parametros[5]->getDefaultValue());
        $this->assertSame('info', $parametros[6]->getDefaultValue());
    }
}
