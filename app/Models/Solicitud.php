<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';
    protected $fillable = ['usuario_id', 'fecha_solicitud', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta', 'total_tiempo', 'tipo_id', 'estado_id', 'recuperable', 'detalle'];

    protected $appends = ['tiempo_total'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function reposiciones()
    {
        return $this->hasMany(ReposicionTiempo::class, 'usuario_id', 'usuario_id');
    }

    public function tipo_solicitud()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_id');
    }

    public function estado_solicitud()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function usuariosEventualidad()
    {
        return $this->hasMany(EventualidadUsuario::class);
    }

    public static function getSolicitudesPorUsuario($tipo)
    {

        $tipo = CatalogoDato::getIdCatalogo($tipo);

        $query = Solicitud::where('tipo_id', $tipo);
        if (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Gerencial')) {
            $solicitudes = $query->orderBy('fecha_solicitud', 'desc')
                ->paginate(15);
        } else {
            $solicitudes = $query->where('usuario_id', auth()->user()->id)
                ->orderBy('fecha_solicitud', 'desc')
                ->paginate(15);
        }
        return  $solicitudes;
    }

    public static function getSumaTotalSolicitudesYReposiciones()
    {
        // Suma total del campo 'total_tiempo' de las solicitudes agrupadas por usuario
        $solicitudes = self::selectRaw('usuario_id, SUM(TIME_TO_SEC(total_tiempo)) as total_segundos')
            ->where('recuperable', true)
            ->whereHas('estado_solicitud', function ($query) {
                $query->where('slug', 'estados.solicitud.aprobado');
            })
            ->groupBy('usuario_id') // Agrupar por usuario
            ->get();

        // Suma total del campo 'total' de las reposiciones agrupadas por usuario
        $reposiciones = ReposicionTiempo::selectRaw('usuario_id, SUM(TIME_TO_SEC(total)) as total_segundos')
            ->whereHas('estado', function ($query) {
                $query->where('slug', 'estados.solicitud.aprobado');
            })
            ->groupBy('usuario_id') // Agrupar por usuario
            ->get();

        // Combinar los resultados de solicitudes y reposiciones
        $resultados = $solicitudes->map(function ($solicitud) use ($reposiciones) {
            $reposicion = $reposiciones->firstWhere('usuario_id', $solicitud->usuario_id);

            $horasSolicitudes = intdiv($solicitud->total_segundos, 3600);
            $minutosSolicitudes = intdiv($solicitud->total_segundos % 3600, 60);

            $horasReposiciones = $reposicion ? intdiv($reposicion->total_segundos, 3600) : 0;
            $minutosReposiciones = $reposicion ? intdiv($reposicion->total_segundos % 3600, 60) : 0;

            return [
                'usuario_id' => $solicitud->usuario_id,
                'suma_total_solicitudes' => sprintf('%d horas y %d minutos', $horasSolicitudes, $minutosSolicitudes),
                'suma_total_reposiciones' => sprintf('%d horas y %d minutos', $horasReposiciones, $minutosReposiciones),
            ];
        });

        return $resultados;
    }

    /**
     * Obtener las solicitudes agrupadas por usuario, con datos de ReposicionTiempo y paginados.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getSolicitudesConReposiciones($usuario = "")
    {
        // Consulta principal: Agrupar por usuario_id y calcular el tiempo total
        $query = self::selectRaw('usuario_id, SUM(TIME_TO_SEC(total_tiempo)) as total_segundos')
            ->with(['reposiciones' => function ($query) {
                // Filtrar o seleccionar campos específicos de ReposicionTiempo si es necesario
                $query->select('id', 'usuario_id', 'fecha', 'hora_desde', 'hora_hasta', 'total')->whereHas('estado', function ($query) {
                    $query->where('slug', 'estados.solicitud.aprobado');
                });
            }])
            ->whereHas('estado_solicitud', function ($query) {
                $query->where('slug', 'estados.solicitud.aprobado');
            })
            ->whereHas('tipo_solicitud', function ($query) {
                $query->where('slug', 'tipo.solicitudes.ausencia');
            })
            ->where('recuperable', true);

        if (!auth()->user()->hasRole(['Administrador', 'Gerencial'])) {
            $query->whereHas('usuario', function ($q) {
                $q->where('id', auth()->user()->id);
            });
        }
        // Filtrar por usuario si el parámetro no está vacío
        if (!empty($usuario)) {
            $query->whereHas('usuario', function ($q) use ($usuario) {
                $q->where('nombre', 'LIKE', "%{$usuario}%"); // Ajusta 'nombre' al campo correspondiente
            });
        }

        // Agrupar por usuario_id y paginar los resultados
        $resultados = $query->groupBy('usuario_id')
            ->paginate(15);

        // Formatear el tiempo total en "H horas y M minutos"
        $resultados->getCollection()->transform(function ($item) {
            $horas = floor($item->total_segundos / 3600);
            $minutos = floor(($item->total_segundos % 3600) / 60);
            $item->tiempo_acumulado_formateado = sprintf('%d horas y %d minutos', $horas, $minutos);

            // Calcular la suma del campo 'total' de las reposiciones
            $sumaTotalReposiciones = 0;
            foreach ($item->reposiciones as $reposicion) {
                $sumaTotalReposiciones += strtotime($reposicion->total) - strtotime('00:00:00');
            }

            // Formatear la suma total de reposiciones
            $horasReposicion = floor($sumaTotalReposiciones / 3600);
            $minutosReposicion = floor(($sumaTotalReposiciones % 3600) / 60);
            $item->timpo_recuperado_formateada = sprintf('%d horas y %d minutos', $horasReposicion, $minutosReposicion);

            return $item;
        });

        return $resultados;
    }

    public function getTiempoTotalAttribute()
    {
        // Combina fecha y hora de inicio y fin
        $inicio = Carbon::parse($this->fecha_desde . ' ' . $this->hora_desde);
        $fin = Carbon::parse($this->fecha_hasta . ' ' . $this->hora_hasta);

        // Calcula la diferencia en minutos
        $minutos = $inicio->diffInMinutes($fin);

        // Convierte a horas y minutos
        $horas = intdiv($minutos, 60);
        $min = $minutos % 60;

        return sprintf('%d:%02d', $horas, $min);
    }

    public static function getTotalTiempoUsuario($usuarioId)
    {
        // Consulta para obtener la suma de total_tiempo en segundos
        $segundosTotales = Solicitud::where('usuario_id', $usuarioId)
            ->where('recuperable', true)
            ->whereHas('estado_solicitud', function ($query) {
                $query->where('slug', 'estados.solicitud.aprobado');
            })
            ->whereHas('tipo_solicitud', function ($query) {
                $query->where('slug', 'tipo.solicitudes.ausencia');
            })
            ->selectRaw('SUM(TIME_TO_SEC(total_tiempo)) as total_segundos')
            ->value('total_segundos') ?? 0;

        // Convertir los segundos totales a horas y minutos
        $horas = intdiv($segundosTotales, 3600); // Horas completas
        $minutos = intdiv($segundosTotales % 3600, 60); // Minutos restantes

        // Formatear el resultado como "H horas y M minutos"
        return sprintf('%d horas y %d minutos', $horas, $minutos);
    }

    public static function dataReporteSolicitudes($request)
    {
        $usuario = $request->input('usuario');
        $fechas = $request->input('fechas');
        $estado = $request->input('estado');
        $recuperable = $request->input('recuperable');
        $tipo_solicitud = $request->input('tipo_solicitud');
        $ordernar = $request->input('ordenado');

        $query = self::select('solicitudes.*')
            ->join('usuarios as usuario', 'solicitudes.usuario_id', '=', 'usuario.id') // Unir la tabla usuarios
            ->with(['usuario', 'tipo_solicitud', 'estado_solicitud']); // Cargar relaciones necesarias

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

        // Filtrar por tipo de solicitud
        $query->when($tipo_solicitud, function ($q, $tipo_solicitud) {
            $q->where('tipo_id', $tipo_solicitud);
        });

        // Filtrar por recuperable
        $query->when($recuperable, function ($q, $recuperable) {
            $recuperable = $recuperable === 'si' ? true : false;
            $q->where('recuperable', $recuperable);
        });

        // Ordenar los resultados
        switch ($ordernar) {
            case 'secuencial':
                $query->orderBy('solicitudes.id', 'asc');
                break;
            case 'fecha':
                $query->orderBy('solicitudes.fecha_solicitud', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('usuario.nombre', 'asc'); // Ordenar por nombre del usuario
                break;
            default:
                $query->orderBy('solicitudes.id', 'asc'); // Valor por defecto
                break;
        }
        return $query->get();
    }

    public function scopeBuscarAusencias($query, $buscar, $slug)
    {
        return $query
            ->with(['usuario', 'estado_solicitud'])
            ->whereHas('tipo_solicitud', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->where(function ($q) use ($buscar) {
                $q->whereHas('usuario', function ($query) use ($buscar) {
                    $query->where('nombre', 'like', "%{$buscar}%");
                })
                    ->orWhereHas('estado_solicitud', function ($query) use ($buscar) {
                        $query->where('descripcion', 'like', "%{$buscar}%");
                    })
                    ->orWhere(function ($q2) use ($buscar) {
                        if ($buscar === 'si') {
                            $q2->where('recuperable', true);
                        } elseif ($buscar === 'no') {
                            $q2->where('recuperable', false);
                        }
                    });
            });
    }
}
