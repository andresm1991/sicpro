<?php

namespace App\Models\JPLimpieza;

use Carbon\Carbon;
use App\Models\Proveedor;
use App\Models\CatalogoDato;
use Illuminate\Database\Eloquent\Model;

class Adquisicion extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'adquisiciones';

    protected $fillable = [
        'fecha',
        'numero',
        'proyecto_id',
        'proveedor_id',
        'tipo_id',
        'estado',
        'nro_factura',
        'archivo',
        'forma_pago_id',
        'administrativo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    protected $appends = ['total_general', 'total_general_formatted', 'fecha_formateada'];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
    public function tipo()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_id');
    }
    public function formaPago()
    {
        return $this->belongsTo(CatalogoDato::class, 'forma_pago_id');
    }
    public function detalles()
    {
        return $this->hasMany(DetalleAdquisicion::class, 'adquisicion_id');
    }

    public function getTotalGeneralAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_con_iva;
        });
    }

    public function getTotalGeneralFormattedAttribute()
    {
        return number_format($this->total_general, 4);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('Y-m-d') : null;
    }

    /**
     * Datos para el reporte de adquisiciones
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function dataReporteAdquisiciones($request)
    {
        // --- PREPARACIÓN DE VARIABLES ---
        // Preparamos las fechas una sola vez para reutilizarlas
        $fechaInicioF = null;
        $fechaFinF = null;
        if ($request->filled('fechas')) { // Usar filled() es más robusto
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicioF = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFinF = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');
        }

        // Guardamos el valor del proyecto para reutilizar la lógica
        $proyectoInput = $request->input('proyecto');
        $proveedor = $request->input('proveedor');

        // 1. OBTENER LAS ADQUISICIONES
        // ===============================================
        $adquisicionesQuery = self::with(['proyecto', 'proveedor', 'tipo', 'formaPago', 'detalles'])
            ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                $q->whereBetween('fecha', [$fechaInicioF, $fechaFinF]);
            })
            ->when($proyectoInput, function ($q) use ($proyectoInput) {
                if (is_numeric($proyectoInput)) {
                    $q->where('proyecto_id', $proyectoInput);
                } else {
                    // Esta lógica ya estaba correcta aquí
                    $q->whereNull('proyecto_id');
                }
            })

            ->when($request->input('tipo'), function ($q) use ($request) {
                $q->where('tipo_id', $request->input('tipo'));
            })
            ->when($proveedor, function ($q) use ($proveedor) {
                $q->where('proveedor_id', $proveedor);
            })
            ->when($request->input('estado'), function ($q) use ($request) {
                $q->where('estado', $request->input('estado'));
            })
            ->when($request->input('forma_pago'), function ($q) use ($request) {
                $q->where('forma_pago_id', $request->input('forma_pago'));
            })
            ->orderBy('fecha', 'desc');

        $adquisicionesAgrupadas = $adquisicionesQuery->get()->groupBy('tipo.descripcion');
        $reportData = $adquisicionesAgrupadas->toArray();

        // 2. OBTENER OTRAS CATEGORÍAS (si no se filtró por un tipo específico)
        // =====================================================================
        if (!$request->filled('tipo')) {

            // --- MANO DE OBRA ---
            $manoDeObra = ManoObra::with('proyecto')
                ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                    $q->where(function ($query) use ($fechaInicioF, $fechaFinF) {
                        $query->where('fecha_desde', '<=', $fechaFinF)
                            ->where('fecha_hasta', '>=', $fechaInicioF);
                    });
                })
                ->when($proyectoInput, function ($q) use ($proyectoInput) {
                    if (is_numeric($proyectoInput)) {
                        $q->where('proyecto_id', $proyectoInput);
                    } else {
                        // Añadimos la lógica que faltaba
                        $q->whereNull('proyecto_id');
                    }
                })
                ->when($proveedor, function ($q) use ($proveedor) {
                    $q->whereHas('detalles', function ($d) use ($proveedor) {
                        $d->where('proveedor_id', $proveedor);
                    });
                })
                ->orderBy('fecha_desde', 'desc')
                ->get();

            if ($manoDeObra->isNotEmpty()) {
                $reportData['Mano de Obra'] = $manoDeObra->toArray();
            }

            // --- CONTRATISTAS ---
            $contratistas = Contratista::with('proyecto')
                ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                    // Asumiendo que Contratista usa una sola 'fecha'
                    $q->whereBetween('fecha', [$fechaInicioF, $fechaFinF]);
                })
                ->when($proyectoInput, function ($q) use ($proyectoInput) {
                    if (is_numeric($proyectoInput)) {
                        $q->where('proyecto_id', $proyectoInput);
                    } else {
                        // Añadimos la lógica que faltaba
                        $q->whereNull('proyecto_id');
                    }
                })
                ->when($proveedor, function ($q) use ($proveedor) {
                    $q->where('proveedor_id', $proveedor);
                })
                ->orderBy('fecha', 'desc')
                ->get();

            if ($contratistas->isNotEmpty()) {
                $reportData['Contratistas'] = $contratistas->toArray();
            }
        }

        return $reportData;
    }
}
