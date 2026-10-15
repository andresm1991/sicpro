<?php

namespace App\Models;

use App\Support\RangoFechas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReposicionTiempo extends Model
{
    use HasFactory;

    protected $table = 'reposicion_tiempos';
    protected $fillable = ['usuario_id', 'fecha', 'hora_desde', 'hora_hasta', 'total', 'estado_id', 'detalle'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación inversa con el modelo Solicitud.
     */
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'usuario_id', 'usuario_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public static function getTotalReposiciones($request)
    {
        $usuario = $request->input('usuario');
        $fechas = $request->input('fechas');
        $estado = $request->input('estado');
        $ordernar = $request->input('ordenado');

        // Parsear fechas una sola vez para reutilizarlas
        $fechaInicioFormatted = null;
        $fechaFinFormatted = null;
        if ($fechas) {
            [$fechaInicioFormatted, $fechaFinFormatted] = RangoFechas::parse($fechas);
        }

        // Consulta principal: Agrupar por usuario_id y calcular el tiempo total
        $query = Solicitud::selectRaw('solicitudes.usuario_id, SUM(TIME_TO_SEC(total_tiempo)) as total_segundos, MAX(fecha_solicitud) as max_fecha')
            ->join('usuarios as usuario', 'solicitudes.usuario_id', '=', 'usuario.id') // Unir la tabla usuarios
            ->with([
                'reposiciones' => function ($query) use ($fechaInicioFormatted, $fechaFinFormatted, $estado) {
                    $query->select('id', 'usuario_id', 'fecha', 'hora_desde', 'hora_hasta', 'total', 'estado_id', 'detalle')
                        ->with('estado', 'usuario');
                    // Filtrar reposiciones por el mismo rango de fechas
                    if ($fechaInicioFormatted && $fechaFinFormatted) {
                        $query->whereBetween('fecha', [$fechaInicioFormatted, $fechaFinFormatted]);
                    }
                    // El filtro de estado tiene que llegar tambien a las filas del
                    // detalle (reposiciones_detallado), no solo al agrupado.
                    if ($estado) {
                        $query->where('estado_id', $estado);
                    }
                },
                'usuario'
            ])
            ->whereHas('estado_solicitud', function ($q) {
                $q->where('slug', 'estados.solicitud.aprobado');
            })
            ->whereHas('tipo_solicitud', function ($q) {
                $q->where('slug', 'tipo.solicitudes.ausencia');
            })
            ->where('recuperable', true);

        $query->when($usuario, function ($q, $usuario) {
            $q->where('solicitudes.usuario_id', $usuario);
        });

        // Filtrar por rango de fechas
        $query->when($fechaInicioFormatted && $fechaFinFormatted, function ($q) use ($fechaInicioFormatted, $fechaFinFormatted) {
            $q->whereBetween('fecha_solicitud', [$fechaInicioFormatted, $fechaFinFormatted]);
        });

        // Filtrar por estado
        $query->when($estado, function ($q, $estado) {
            $q->whereHas('reposiciones', function ($query) use ($estado) {
                return $query->where('estado_id', $estado);
            });
        });

        // Ordenar los resultados
        switch ($ordernar) {
            case 'secuencial':
                $query->orderBy('solicitudes.usuario_id', 'asc'); // Ordenar por usuario_id
                break;
            case 'fecha':
                $query->orderBy('max_fecha', 'asc'); // Usar el alias max_fecha
                break;
            case 'alfabetico':
                $query->orderBy('usuario.nombre', 'asc'); // Ordenar por nombre del usuario
                break;
            default:
                $query->orderBy('solicitudes.usuario_id', 'asc'); // Valor por defecto
                break;
        }

        // Agrupar por usuario_id
        $resultados = $query->groupBy('solicitudes.usuario_id')->get();

        // Transformar los datos
        return $resultados->map(function ($item) {
            $minutos = 0;
            $horas = 0;
            $sumaTotalReposiciones = 0;

            $horas = floor($item->total_segundos / 3600);
            $minutos = floor(($item->total_segundos % 3600) / 60);

            // Calcular la suma del campo 'total' de las reposiciones solo si el estado es 'aprobado'
            foreach ($item->reposiciones as $reposicion) {
                if ($reposicion->estado && $reposicion->estado->slug === 'estados.solicitud.aprobado') {
                    $sumaTotalReposiciones += strtotime($reposicion->total) - strtotime('00:00:00');
                }
            }

            $item->tiempo_acumulado_formateado = sprintf('%d horas y %d minutos', $horas, $minutos);
            $item->totalGeneral = $item->reposiciones->map(function ($item) {
                return $item->total;
            });

            // Formatear la suma total de reposiciones
            $horasReposicion = floor($sumaTotalReposiciones / 3600);
            $minutosReposicion = floor(($sumaTotalReposiciones % 3600) / 60);
            $item->tiempo_recuperado_formateado = sprintf('%d horas y %d minutos', $horasReposicion, $minutosReposicion);

            return $item;
        });
    }
}
