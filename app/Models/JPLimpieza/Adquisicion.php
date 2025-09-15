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
        $fechaInicioF = null;
        $fechaFinF = null;
        if ($request->input('fechas')) {
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicioF = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFinF = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');
        }
        // 1. OBTENER LAS ADQUISICIONES Y AGRUPARLAS
        // ===============================================

        // Construimos la consulta base para Adquisiciones
        $adquisicionesQuery = self::with(['proyecto', 'proveedor', 'tipo', 'formaPago', 'detalles'])
            ->when($request->input('fechas'), function ($q) use ($request) {
                list($fechaInicio, $fechaFin) = explode(' - ', $request->input('fechas'));
                $q->whereBetween('fecha', [
                    Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d'),
                    Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d')
                ]);
            })
            ->when($request->input('proyecto'), function ($q) use ($request) {
                if (is_numeric($request->input('proyecto'))) {
                    $q->where('proyecto_id', $request->input('proyecto'));
                } else {
                    $q->whereNull('proyecto_id');
                }
            })
            ->when($request->input('tipo'), function ($q) use ($request) {
                $q->where('tipo_id', $request->input('tipo'));
            })
            ->when($request->input('proveedor'), function ($q) use ($request) {
                $q->where('proveedor_id', $request->input('proveedor'));
            })
            ->when($request->input('estado'), function ($q) use ($request) {
                $q->where('estado', $request->input('estado'));
            })
            ->when($request->input('forma_pago'), function ($q) use ($request) {
                $q->where('forma_pago_id', $request->input('forma_pago'));
            })
            ->orderBy('fecha', 'desc');

        // Ejecutamos la consulta y agrupamos los resultados por el nombre del tipo.
        // Usamos la relación 'tipo' y accedemos a su propiedad 'nombre' (o como se llame).
        $adquisicionesAgrupadas = $adquisicionesQuery->get()->groupBy('tipo.descripcion');

        // Inicializamos el array final con las adquisiciones ya agrupadas.
        $reportData = $adquisicionesAgrupadas->toArray();

        // 2. OBTENER OTRAS CATEGORÍAS (si no se filtró por un tipo específico)
        // =====================================================================
        // Solo buscaremos Mano de Obra y Contratistas si el usuario NO está pidiendo
        // un tipo de adquisición en particular.
        if (!$request->has('tipo') || empty($request->input('tipo'))) {

            // --- MANO DE OBRA ---
            $manoDeObra = ManoObra::with('proyecto') // Carga relaciones si las necesitas
                ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                    // AQUÍ ESTÁ LA CORRECCIÓN CLAVE
                    // Busca registros cuyo rango [fecha_desde, fecha_hasta] se solape
                    // con el rango del filtro [$fechaInicioF, $fechaFinF].
                    $q->where(function ($query) use ($fechaInicioF, $fechaFinF) {
                        $query->where('fecha_desde', '<=', $fechaFinF)
                            ->where('fecha_hasta', '>=', $fechaInicioF);
                    });
                })
                ->when($request->input('proyecto'), function ($q) use ($request) {
                    if (is_numeric($request->input('proyecto'))) {
                        $q->where('proyecto_id', $request->input('proyecto'));
                    }
                })
                ->orderBy('fecha_desde', 'desc')
                ->get();

            if ($manoDeObra->isNotEmpty()) {
                $reportData['Mano de Obra'] = $manoDeObra->toArray();
            }

            // --- CONTRATISTAS ---
            $contratistas = Contratista::with('proyecto') // Carga relaciones si las necesitas
                ->when($request->input('fechas'), function ($q) use ($request) {
                    list($fechaInicio, $fechaFin) = explode(' - ', $request->input('fechas'));
                    $q->whereBetween('fecha', [ // Asume que Contratista tiene una columna 'fecha'
                        Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d'),
                        Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d')
                    ]);
                })
                ->when($request->input('proyecto'), function ($q) use ($request) {
                    if (is_numeric($request->input('proyecto'))) {
                        $q->where('proyecto_id', $request->input('proyecto'));
                    }
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