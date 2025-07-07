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
        'subproyecto',
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
        $subproyecto = $request->input('subproyecto');

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

        $query->when($subproyecto, function ($q, $subproyecto) {
            $q->where('subproyecto', $subproyecto);
        });

        // Filtrar por necesidad
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
            });
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
                $detalles = $adquisicion->adquisiciones_detalle->where('articulo_id', $producto);
                $cantidad = $detalles->sum('cantidad_solicitada');
                $total = $detalles->sum(function ($detalle) {
                    $cantidad = $detalle->cantidad_solicitada ?? 0;
                    $valor = $detalle->valor ?? 0;
                    $iva = $detalle->iva ?? 0;
                    return calcularTotalProducto($cantidad, $valor, $iva);
                });
                $necesidad = implode(', ', $detalles->pluck('necesidad')->toArray());
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

    /**
     * Reporte global
     */

    public static function reporteGlobalAdquisiciones($request)
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
        $subproyecto = $request->input('subproyecto');

        $query = self::with(['proyecto', 'etapa', 'tipo_etapa', 'orden_recepcion', 'orden_recepcion.forma_pago', 'orden_recepcion.proveedor', 'proyecto.contratista', 'proyecto.mano_obra']);

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

        $query->when($subproyecto, function ($q, $subproyecto) {
            $q->where('subproyecto', $subproyecto);
        });

        // Filtrar por necesidad
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

        // Filtrar por la forma de pago
        $query->when($forma_pago, function ($q, $forma_pago) {
            $q->whereHas('orden_recepcion', function ($query) use ($forma_pago) {
                $query->where('forma_pago_id', $forma_pago);
            });
        });

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

                break;
        }
        $resultado = [];

        foreach ($query->get() as $adquisicion) {
            $proyecto = $adquisicion->proyecto;
            $proyectoNombre = $proyecto->nombre_proyecto ?? 'Sin proyecto';
            $etapaNombre = $adquisicion->etapa->descripcion ?? 'Sin etapa';

            if (!isset($resultado[$proyectoNombre][$etapaNombre])) {
                // Contratistas del proyecto y etapa
                $contratistas = $proyecto->contratista ?? collect();
                $contratistas = $contratistas->when($subproyecto, function ($collection) use ($subproyecto) {
                    return $collection->where('subproyecto', $subproyecto);
                });
                // APLICAR FILTRO POR PROVEEDOR
                if ($proveedor) {
                    $contratistas = $contratistas->where('proveedor_id', $proveedor);
                }
                $contratistasArray = [];
                foreach ($contratistas as $contratista) {
                    // Solo incluir si la etapa coincide
                    if (($contratista->etapa_id ?? null) == $adquisicion->etapa_id) {
                        $totalContratado = $contratista->detalle_contratistas->sum(function ($detalle) use ($contratista) {
                            $cantidad = $detalle->cantidad ?? 0;
                            $valor = $detalle->valor_unitario ?? 0;
                            return $cantidad * $valor * ($contratista->numero_casas ?? 1);
                        });
                        $pagos = $contratista->pagosOrdenTrabajoContratista->sum(function ($pago) {
                            return $pago->pagado ? $pago->valor : 0;
                        });
                        $saldo = $totalContratado - $pagos;

                        $categoriaNombre = $contratista->articulo->descripcion ?? 'Sin categoría';
                        $proveedorNombre = $contratista->proveedor->razon_social ?? 'Sin proveedor';

                        // Clave única por proveedor y categoría
                        $key = $proveedorNombre . '|' . $categoriaNombre;

                        if (!isset($contratistasArray[$key])) {
                            $contratistasArray[$key] = [
                                'proveedor' => $proveedorNombre,
                                'categoria' => $categoriaNombre,
                                'cantidad' => $contratista->numero_casas ?? 0, // cantidad del modelo Contratista
                                'total_contratado' => $totalContratado,
                                'pagos' => $pagos,
                                'saldo' => $saldo,
                            ];
                        } else {
                            // Si hay más de un registro para el mismo proveedor/categoría, suma los valores
                            $contratistasArray[$key]['cantidad'] += $contratista->numero_casas ?? 0;
                            $contratistasArray[$key]['total_contratado'] += $totalContratado;
                            $contratistasArray[$key]['pagos'] += $pagos;
                            $contratistasArray[$key]['saldo'] += $saldo;
                        }
                    }
                }
                $contratistasArray = array_values($contratistasArray);

                // Mano de obra del proyecto y etapa
                $manosObra = $proyecto->mano_obra ?? collect();
                $manosObra = $manosObra->when($subproyecto, function ($collection) use ($subproyecto) {
                    return $collection->where('subproyecto', $subproyecto);
                });
                // APLICAR FILTRO POR PROVEEDOR
                if ($proveedor) {
                    $manosObra = $manosObra->where('proveedor_id', $proveedor);
                }
                $manosObraEtapa = $manosObra->where('etapa_id', $adquisicion->etapa_id);
                $uniqueFechas = $manosObraEtapa->unique(function ($item) {
                    return $item->fecha_inicio . '|' . $item->fecha_fin;
                });

                $detalleManoObra = $manosObraEtapa->flatMap->detalle_mano_obra;

                $detalleManoObraPagados = $detalleManoObra->filter(function ($detalle) {
                    // Solo incluir si existe relación con pago_mano_obra
                    return $detalle->mano_obra && $detalle->mano_obra->pago_mano_obra && $detalle->mano_obra->pago_mano_obra->count() > 0;
                });

                $totalManoObra = $detalleManoObraPagados->sum(function ($detalle) {
                    return ($detalle->valor ?? 0) + ($detalle->adicional ?? 0) - ($detalle->descuento ?? 0);
                });


                $resultado[$proyectoNombre][$etapaNombre] = [
                    'contratista' => $contratistasArray,
                    'mano_obra' => [[
                        'cantidad' => $uniqueFechas->count(),
                        'total' => $totalManoObra
                    ]],
                    'materiales_herramientas' => [],
                    'servicios' => [],
                ];
            }

            foreach ($adquisicion->adquisiciones_detalle as $detalle) {
                $articuloId = $detalle->articulo_id;
                $articuloNombre = $detalle->producto->descripcion ?? 'Sin nombre';
                $unidadMedida = $detalle->unidad_medida->descripcion ?? 'Sin unidad';
                $tipoEtapa = $adquisicion->tipo_etapa->slug;

                $cantidad = $detalle->cantidad_solicitada ?? 0;
                $valor = $detalle->valor ?? 0;
                $iva = $detalle->iva ?? 0;
                $totalDetalle =  calcularTotalProducto($cantidad, $valor, $iva);

                $articuloData = [
                    'articulo_id'    => $articuloId,
                    'articulo'       => $articuloNombre,
                    'unidad_medida'  => $unidadMedida,
                    'cantidad_total' => $cantidad,
                    'total'          => $totalDetalle,
                ];

                // Separar por tipo_etapa_id
                if ($tipoEtapa == 'meteriales.herramientas') {
                    $key = array_search($articuloId, array_column($resultado[$proyectoNombre][$etapaNombre]['materiales_herramientas'], 'articulo_id'));
                    if ($key === false) {
                        $resultado[$proyectoNombre][$etapaNombre]['materiales_herramientas'][] = $articuloData;
                    } else {
                        $resultado[$proyectoNombre][$etapaNombre]['materiales_herramientas'][$key]['cantidad_total'] += $cantidad;
                        $resultado[$proyectoNombre][$etapaNombre]['materiales_herramientas'][$key]['total'] += $totalDetalle;
                    }
                } else { // 2 = servicios (ajusta el valor según tu catálogo)
                    $key = array_search($articuloId, array_column($resultado[$proyectoNombre][$etapaNombre]['servicios'], 'articulo_id'));
                    if ($key === false) {
                        $resultado[$proyectoNombre][$etapaNombre]['servicios'][] = $articuloData;
                    } else {
                        $resultado[$proyectoNombre][$etapaNombre]['servicios'][$key]['cantidad_total'] += $cantidad;
                        $resultado[$proyectoNombre][$etapaNombre]['servicios'][$key]['total'] += $totalDetalle;
                    }
                }
            }
        }

        // 1. Ordenar los proyectos (claves principales) alfabéticamente
        ksort($resultado, SORT_STRING);

        // 2. Iterar para ordenar internamente cada nivel
        foreach ($resultado as $proyectoNombre => &$etapas) {
            // 2.1. Ordenar las etapas (claves secundarias) alfabéticamente
            ksort($etapas, SORT_STRING);

            foreach ($etapas as $etapaNombre => &$categorias) {
                // 2.2. Ordenar los artículos IGNORANDO MAYÚSCULAS/MINÚSCULAS
                if (!empty($categorias['materiales_herramientas'])) {
                    usort($categorias['materiales_herramientas'], function ($a, $b) {
                        return strnatcasecmp($a['articulo'], $b['articulo']);
                    });
                }
                if (!empty($categorias['servicios'])) {
                    usort($categorias['servicios'], function ($a, $b) {
                        return strnatcasecmp($a['articulo'], $b['articulo']);
                    });
                }
                if (!empty($categorias['contratista'])) {
                    usort($categorias['contratista'], function ($a, $b) {
                        $proveedorCmp = strnatcasecmp($a['proveedor'], $b['proveedor']);
                        if ($proveedorCmp !== 0) {
                            return $proveedorCmp;
                        }
                        return strnatcasecmp($a['categoria'], $b['categoria']);
                    });
                }
            }
        }
        unset($etapas, $categorias); // Buenas prácticas para limpiar referencias

        return $resultado;
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
