<?php

namespace App\Models;

use Carbon\Carbon;
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

        // Consulta principal: Agrupar por usuario_id y calcular el tiempo total
        $query = Solicitud::selectRaw('solicitudes.usuario_id, SUM(TIME_TO_SEC(total_tiempo)) as total_segundos, MAX(fecha_solicitud) as max_fecha')
            ->join('usuarios as usuario', 'solicitudes.usuario_id', '=', 'usuario.id') // Unir la tabla usuarios
            ->with([
                'reposiciones' => function ($query) {
                    $query->select('id', 'usuario_id', 'fecha', 'hora_desde', 'hora_hasta', 'total', 'estado_id', 'detalle')->with('estado', 'usuario');
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
        $query->when($fechas, function ($q) use ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d');
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

    public static function dataReporteReposiciones($request)
    {
        $usuario = $request->input('usuario');
        $fechas = $request->input('fechas');
        $estado = $request->input('estado');
        $ordernar = $request->input('ordenado');

        $query = self::select('reposicion_tiempos.*')
            ->join('usuarios as usuario', 'reposicion_tiempos.usuario_id', '=', 'usuario.id') // Unir la tabla usuarios
            ->with(['usuario', 'estado']); // Cargar relaciones necesarias

        // Filtrar por usuario
        $query->when($usuario, function ($q, $usuario) {
            $q->where('usuario_id', $usuario);
        });

        // Filtrar por rango de fechas
        $query->when($fechas, function ($q) use ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d');
            $q->whereBetween('fecha', [$fechaInicioFormatted, $fechaFinFormatted]);
        });

        // Filtrar por estado
        $query->when($estado, function ($q, $estado) {
            $q->where('estado_id', $estado);
        });

        // Ordenar los resultados
        switch ($ordernar) {
            case 'secuencial':
                $query->orderBy('reposicion_tiempos.id', 'asc');
                break;
            case 'fecha':
                $query->orderBy('reposicion_tiempos.fecha', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('usuario.nombre', 'asc'); // Ordenar por nombre del usuario
                break;
            default:
                $query->orderBy('reposicion_tiempos.id', 'asc'); // Valor por defecto
                break;
        }
        return $query->get();
    }


    public static function dataReporteSolicitudesYReposiciones($request)
    {
        $usuario = $request->input('usuario');
        $fechas = $request->input('fechas');
        $estado = $request->input('estado');
        $ordernar = $request->input('ordenado');

        // Construir la consulta principal para solicitudes
        $querySolicitudes = Solicitud::select('solicitudes.*')
            ->join('usuarios as usuario', 'solicitudes.usuario_id', '=', 'usuario.id') // Unir la tabla usuarios
            ->with(['usuario', 'estado_solicitud', 'reposiciones']); // Cargar relaciones necesarias

        // Filtrar por usuario
        $querySolicitudes->when($usuario, function ($q, $usuario) {
            $q->where('usuario_id', $usuario);
        });

        // Filtrar por rango de fechas
        $querySolicitudes->when($fechas, function ($q) use ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d');
            $q->whereBetween('fecha_solicitud', [$fechaInicioFormatted, $fechaFinFormatted]);
        });

        // Filtrar por estado
        $querySolicitudes->when($estado, function ($q, $estado) {
            $q->where('estado_id', $estado);
        });

        // Construir la consulta principal para reposiciones
        $queryReposiciones = ReposicionTiempo::select('reposicion_tiempos.*')
            ->join('usuarios as usuario', 'reposicion_tiempos.usuario_id', '=', 'usuario.id') // Unir la tabla usuarios
            ->with(['usuario', 'estado']); // Cargar relaciones necesarias

        // Filtrar por usuario
        $queryReposiciones->when($usuario, function ($q, $usuario) {
            $q->where('usuario_id', $usuario);
        });

        // Filtrar por rango de fechas
        $queryReposiciones->when($fechas, function ($q) use ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d');
            $q->whereBetween('fecha', [$fechaInicioFormatted, $fechaFinFormatted]);
        });

        // Filtrar por estado
        $queryReposiciones->when($estado, function ($q, $estado) {
            $q->where('estado_id', $estado);
        });

        // Ordenar los resultados
        switch ($ordernar) {
            case 'secuencial':
                $querySolicitudes->orderBy('solicitudes.id', 'asc');
                $queryReposiciones->orderBy('reposicion_tiempos.id', 'asc');
                break;
            case 'fecha':
                $querySolicitudes->orderBy('solicitudes.fecha_solicitud', 'asc');
                $queryReposiciones->orderBy('reposicion_tiempos.fecha', 'asc');
                break;
            case 'alfabetico':
                $querySolicitudes->orderBy('usuario.nombre', 'asc');
                $queryReposiciones->orderBy('usuario.nombre', 'asc');
                break;
            default:
                $querySolicitudes->orderBy('solicitudes.id', 'asc');
                $queryReposiciones->orderBy('reposicion_tiempos.id', 'asc');
                break;
        }

        // Obtener los resultados de ambas consultas
        $solicitudes = $querySolicitudes->get();
        $reposiciones = $queryReposiciones->get();

        // Combinar los resultados en un solo arreglo
        $resultados = $solicitudes->map(function ($solicitud) {
            return [
                'tipo' => 'solicitud',
                'id' => $solicitud->id,
                'usuario' => $solicitud->usuario->nombre,
                'fecha' => $solicitud->fecha_solicitud,
                'estado' => $solicitud->estado_solicitud->descripcion,
                'detalle' => $solicitud->detalle,
            ];
        })->merge(
                $reposiciones->map(function ($reposicion) {
                    return [
                        'tipo' => 'reposicion',
                        'id' => $reposicion->id,
                        'usuario' => $reposicion->usuario->nombre,
                        'fecha' => $reposicion->fecha,
                        'estado' => $reposicion->estado->descripcion,
                        'detalle' => $reposicion->detalle,
                    ];
                })
            );

        return $resultados->sortBy($ordernar === 'fecha' ? 'fecha' : 'id')->values();
    }
}