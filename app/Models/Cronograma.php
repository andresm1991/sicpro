<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cronograma extends Model
{
    use HasFactory;
    protected $table = 'cronograma';
    protected $fillable = ['proyecto_id', 'rubro_cronograma_id', 'semana', 'dia', 'observacion'];

    public function rubro_cronograma()
    {
        return $this->belongsTo(RubroCronograma::class, 'rubro_cronograma_id');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public static function TotalAdquisicionesEtapa($proyectoId, $slug)
    {
        $total_aquisiciones = AdquisicionDetalle::whereHas('adquisicion', function ($query) use ($proyectoId, $slug) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
        })->sum('valor');


        $total_mano_obra = DetalleManoObra::whereHas('mano_obra', function ($query) use ($proyectoId, $slug) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
        })
            ->whereHas('mano_obra.pago_mano_obra') // Filtrar solo los detalles relacionados con PagoManoObra
            ->sum('valor');

        $total_contratista = PagoOrdenTrabajoContratista::whereHas('contratista', function ($query) use ($proyectoId, $slug) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
        })->where('pagado', true)
            ->sum('valor');

        return ['totalAdquisiciones' => $total_aquisiciones, 'totalManoObra' => $total_mano_obra, 'totalContratista' => $total_contratista];
    }
}
