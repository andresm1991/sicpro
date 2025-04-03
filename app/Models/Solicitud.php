<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';
    protected $fillable = ['usuario_id', 'fecha_solicitud', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta', 'total_tiempo', 'tipo_id', 'estado_id', 'recuperable', 'detalle'];

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

    public static function getSolicitudesPorUsuario()
    {

        if (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Gerencial')) {
            $solicitudes = Solicitud::orderBy('fecha_solicitud', 'desc')
                ->paginate(15);
        } else {
            $solicitudes = Solicitud::where('usuario_id', auth()->user()->id)
                ->orderBy('fecha_solicitud', 'desc')
                ->paginate(15);
        }
        return  $solicitudes;
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
                $query->select('id', 'usuario_id', 'fecha', 'hora_desde', 'hora_hasta', 'total');
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
            $item->tiempo_formateado = sprintf('%d horas y %d minutos', $horas, $minutos);

            // Calcular la suma del campo 'total' de las reposiciones
            $sumaTotalReposiciones = 0;
            foreach ($item->reposiciones as $reposicion) {
                $sumaTotalReposiciones += strtotime($reposicion->total) - strtotime('00:00:00');
            }

            // Formatear la suma total de reposiciones
            $horasReposicion = floor($sumaTotalReposiciones / 3600);
            $minutosReposicion = floor(($sumaTotalReposiciones % 3600) / 60);
            $item->suma_reposiciones_formateada = sprintf('%d horas y %d minutos', $horasReposicion, $minutosReposicion);

            return $item;
        });

        return $resultados;
    }

    public static function getTotalTiempoUsuario($usuarioId)
    {
        // Consulta para obtener la suma de total_tiempo en segundos
        $segundosTotales = Solicitud::where('usuario_id', $usuarioId)
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
}
