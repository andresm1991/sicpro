<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\ReposicionTiempo;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reporte de reposiciones (ReposicionTiempo::getTotalReposiciones).
 *
 * El filtro "estado" se aplicaba solo a la consulta que agrupa por usuario:
 * las filas del detalle (reposiciones_detallado) se cargaban sin filtrar, asi
 * que la tabla mostraba reposiciones de todos los estados.
 */
class ReporteReposicionesEstadoTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/reportes/visulizar-reporte-solicitudes';

    private ?User $usuario = null;

    private function usuario(): User
    {
        if ($this->usuario === null) {
            $this->usuario = User::factory()->create([
                'usuario' => 'usuario_reposiciones_test',
                'correo' => 'usuario_reposiciones_test@example.test',
            ]);
        }

        return $this->usuario;
    }

    private function estado(string $slug, string $descripcion): CatalogoDato
    {
        return CatalogoDato::create([
            'descripcion' => $descripcion,
            'detalle' => '',
            'slug' => $slug,
            'padre_id' => null,
            'activo' => true,
        ]);
    }

    /**
     * @return array{0:CatalogoDato,1:CatalogoDato}
     */
    private function solicitudConDosReposiciones(): array
    {
        $usuario = $this->usuario();
        $tipoAusencia = $this->estado('tipo.solicitudes.ausencia', 'Ausencia');
        $solicitudAprobada = $this->estado('estados.solicitud.aprobado', 'Aprobado');
        $reposicionAprobada = $this->estado('estados.solicitud.aprobado.reposicion', 'Aprobado');
        $reposicionPendiente = $this->estado('estados.solicitud.pendiente', 'Pendiente');

        Solicitud::create([
            'usuario_id' => $usuario->id,
            'fecha_solicitud' => '2025-03-15',
            'fecha_desde' => '2025-03-15',
            'fecha_hasta' => '2025-03-15',
            'hora_desde' => '08:00:00',
            'hora_hasta' => '10:00:00',
            'total_tiempo' => '02:00:00',
            'tipo_id' => $tipoAusencia->id,
            'estado_id' => $solicitudAprobada->id,
            'recuperable' => true,
        ]);

        $this->reposicion($usuario, $reposicionAprobada, 'aprobada');
        $this->reposicion($usuario, $reposicionPendiente, 'pendiente');

        return [$reposicionAprobada, $reposicionPendiente];
    }

    private function reposicion(User $usuario, CatalogoDato $estado, string $detalle): void
    {
        ReposicionTiempo::create([
            'usuario_id' => $usuario->id,
            'fecha' => '2025-03-20',
            'hora_desde' => '08:00:00',
            'hora_hasta' => '10:00:00',
            'total' => '02:00:00',
            'estado_id' => $estado->id,
            'detalle' => $detalle,
        ]);
    }

    /**
     * @return array<int,int> ids de estado de las filas del detalle
     */
    private function estadosDeLasFilasDelDetalle(array $parametros): array
    {
        $response = $this->actingAs($this->usuario())
            ->post(self::URL, ['tipo_solicitud' => 'reposiciones_detallado'] + $parametros);

        $response->assertOk()->assertJson(['success' => true]);

        return collect($response->json('result'))
            ->flatMap(fn ($item) => $item['reposiciones'])
            ->pluck('estado_id')
            ->all();
    }

    public function test_el_filtro_de_estado_llega_a_las_filas_del_detalle(): void
    {
        [$aprobada, $pendiente] = $this->solicitudConDosReposiciones();

        $estados = $this->estadosDeLasFilasDelDetalle(['estado' => $pendiente->id]);

        $this->assertSame(
            [$pendiente->id],
            $estados,
            'La tabla detallada tiene que mostrar solo las reposiciones del estado filtrado.'
        );
        $this->assertNotContains($aprobada->id, $estados);
    }

    public function test_sin_filtro_de_estado_el_detalle_sigue_mostrando_todas_las_reposiciones(): void
    {
        [$aprobada, $pendiente] = $this->solicitudConDosReposiciones();

        $estados = $this->estadosDeLasFilasDelDetalle([]);

        sort($estados);
        $esperados = [$aprobada->id, $pendiente->id];
        sort($esperados);

        $this->assertSame($esperados, $estados);
    }
}
