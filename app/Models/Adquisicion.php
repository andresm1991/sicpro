<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adquisicion extends Model
{
    use HasFactory;
    protected $table = 'adquisiciones';
    protected $fillable = [
        'fecha',
        'numero',
        'proyecto_id',
        'etapa_id',
        'tipo_etapa_id',
        'usuario_id',
        'estado',
        'tipo_adquisicion',
        'factura',
        'archivo',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function etapa()
    {
        return $this->belongsTo(CatalogoDato::class, 'etapa_id');
    }

    public function tipo_etapa()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_etapa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function adquisiciones_detalle()
    {
        return $this->hasMany(AdquisicionDetalle::class);
    }

    public function orden_recepcion()
    {
        return $this->hasOne(OrdenRecepcion::class, 'adquisicion_id');
    }


    public static function dataReporteAdquisiciones($request, $gasolina = false)
    {
        $ordenado = $request->input('ordenado');
        $fechas = $request->input('fechas');
        $estado = $request->input('estado');
        $proyecto = $request->input('proyecto');
        $etapa = $request->input('etapa');
        $tipo = $request->input('tipo');
        $necesidad = $request->input('necesidad');
        $costo = $request->input('costo');
        $proveedor = $request->input('proveedor');
        $producto = $request->input('producto');
        $tipo_reporte = $request->input('tipo_reporte');
        $forma_pago = $request->input('forma_pago');

        $query = self::with(['proyecto', 'etapa', 'tipo_etapa', 'orden_recepcion', 'orden_recepcion.forma_pago', 'orden_recepcion.proveedor']);

        if ($request->filled('proyecto')) {
            $query->where('proyecto_id', $proyecto);
        }

        $query->when($estado, function ($q, $estado) {
            if ($estado == 'pendientes') {
                $q->whereIn('estado', ['En Proceso', 'Finalizado']);
            } else {
                $q->where('estado', 'Completado');
            }
        });

        // Filtrar por proyecto
        /*$query->when($proyecto, function ($q, $proyecto) {
            $q->where('proyecto_id', $proyecto);
        });*/

        // Filtrar por etapa
        $query->when($etapa, function ($q, $etapa) {
            $q->where('etapa_id', $etapa);
        });

        // Filtrar por tipo_etapa
        $query->when($tipo, function ($q, $tipo) {
            $q->where('tipo_etapa_id', $tipo);
        });

        // Filtrar por costo (INDIRECTOS O DIRECTOS)
        $query->when($costo, function ($q, $costo) {
            $q->where('etapa_id', $costo);
        });

        $query->when($necesidad, function ($q, $necesidad) {
            $q->whereHas('adquisiciones_detalle', function ($query) use ($necesidad) {
                $query->where('necesidad', $necesidad);
            })->with(['adquisiciones_detalle' => function ($query) use ($necesidad) {
                $query->select('adquisicion_id', 'cantidad_solicitada', 'articulo_id')
                    ->where('necesidad', $necesidad); // Filtrar por el producto específico
            }]);
        });

        // Filtrar por producto y obtener la cantidad
        $query->when($producto, function ($q, $producto) {
            $q->whereHas('adquisiciones_detalle', function ($query) use ($producto) {
                $query->where('articulo_id', $producto);
            })->with(['adquisiciones_detalle' => function ($query) use ($producto) {
                $query->where('articulo_id', $producto); // Filtrar por el producto específico
            }]);
        });

        // Filtrar por proveedor
        $query->when($proveedor, function ($q, $proveedor) {
            $q->whereHas('orden_recepcion', function ($query) use ($proveedor) {
                $query->where('proveedor_id', $proveedor);
            });
        });

        // Filtrar por rango de fechas
        $query->when($fechas, function ($q) use ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d');
            $q->whereBetween('fecha', [$fechaInicioFormatted, $fechaFinFormatted]);
        });

        $query->when($tipo_reporte, function ($q, $tipo_reporte) {
            $q->where('tipo_adquisicion', $tipo_reporte);
        });

        // Filtrar por la forma de pago
        $query->when($forma_pago, function ($q, $forma_pago) {
            $q->whereHas('orden_recepcion', function ($query) use ($forma_pago) {
                $query->where('forma_pago_id', $forma_pago);
            });
        });

        // Filtrar por gasolina
        if ($gasolina) {
            $query->whereHas('adquisiciones_detalle', function ($q) {
                $q->whereHas('producto', function ($query) {
                    $query->where('descripcion', 'gasolina para camioneta');
                })->where('kilometraje', '>', 0)->orderBy('kilometraje', 'asc'); // artículo de gasolina
            });
        }

        // Ordenar por secuencial u otro criterio
        switch ($ordenado) {
            case 'secuencial':
                $query->orderBy('numero', 'asc');
                break;
            case 'fecha':
                $query->orderBy('fecha', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('tipo_adquisicion', 'asc');
                break;
            default:
                if ($gasolina) {
                    $query->orderBy(
                        AdquisicionDetalle::select('kilometraje')
                            ->whereColumn('adquisicion_id', 'adquisiciones.id')
                            ->orderBy('kilometraje', 'asc')
                            ->limit(1),
                        'asc'
                    );
                }
                break;
        }

        return $query->get()->map(function ($adquisicion) use ($producto, $gasolina) {
            $necesidad = '';
            // Si se filtra por producto, calcular el total solo para ese producto
            if ($producto) {
                $detalle = $adquisicion->adquisiciones_detalle->firstWhere('articulo_id', $producto);
                $cantidad = $detalle->cantidad_solicitada ?? 0;
                $valor = $detalle->valor ?? 0;
                $iva = $detalle->iva ?? 0;

                $total = calcularTotalProducto($cantidad, $valor, $iva);
                $necesidad .= $detalle->necesidad ?? '';
            } else {
                // Si no se filtra por producto, calcular el total para todos los detalles
                $total = $adquisicion->adquisiciones_detalle->sum(function ($detalle) use ($necesidad) {
                    $cantidad = $detalle->cantidad_solicitada ?? 0;
                    $valor = $detalle->valor ?? 0;
                    $iva = $detalle->iva ?? 0;
                    return calcularTotalProducto($cantidad, $valor, $iva);
                });
                $cantidad =  0; // No aplica cantidad específica si no se filtra por producto


                $necesidad = implode(', ', $adquisicion->adquisiciones_detalle()->pluck('necesidad')->toArray());
            }

            return [
                'adquisicion' => $adquisicion,
                'cantidad' => $cantidad,
                'total' => '$ ' . number_format($total, 4),
                'necesidad' => $necesidad,
            ];
        });
    }

    public function getSemanasAttribute()
    {
        $primerRegistro = $this->proyecto->mano_obra('created_at', 'asc')
            ->first();

        return obtenerSemanasEntreFechas($primerRegistro->fecha_inicio, $this->fecha);

        //return ceil((Carbon::parse($primerRegistro->fecha_inicio)->diffInDays(now()) + 1) / 7);
        //return semanasTranscurridas($primerRegistro->fecha_inicio);
    }
}
