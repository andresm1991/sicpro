<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        $total_adquisiciones = Adquisicion::where('proyecto_id', $proyectoId)
            ->where('estado', 'Completado')
            ->whereHas('etapa', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->with('adquisiciones_detalle.producto') // Cargar relaciones necesarias
            ->get() // Obtener todas las adquisiciones completadas
            ->sum(function ($adquisicion) {
                return $adquisicion->adquisiciones_detalle->sum(function ($detalle) {
                    $iva = $detalle->iva ?? 0; // Usar el operador null coalescing
                    return calcularTotalProducto(
                        $detalle->cantidad_solicitada,
                        $detalle->valor,
                        $iva
                    );
                });
            });

        $total_mano_obra = DetalleManoObra::whereHas('mano_obra', function ($query) use ($proyectoId, $slug) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
        })
            ->whereHas('mano_obra.pago_mano_obra') // Filtrar solo los detalles relacionados con PagoManoObra
            ->sum(DB::raw('COALESCE(valor, 0) + COALESCE(adicional, 0) - COALESCE(descuento, 0)'));

        $total_contratista = PagoOrdenTrabajoContratista::whereHas('contratista', function ($query) use ($proyectoId, $slug) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
        })->where('pagado', true)
            ->sum('valor');

        $total_general = $total_adquisiciones + $total_mano_obra + $total_contratista;


        return ['totalAdquisiciones' => $total_adquisiciones, 'totalManoObra' => $total_mano_obra, 'totalContratista' => $total_contratista, 'total_general' => $total_general];
    }
}