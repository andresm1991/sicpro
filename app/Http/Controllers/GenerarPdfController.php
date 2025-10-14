<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Tarea;
use setasign\Fpdi\Fpdi;
use App\Models\Articulo;
use App\Models\ManoObra;
use App\Models\Proyecto;
use App\Models\Proveedor;
use App\Models\Solicitud;
use App\Models\Cronograma;
use App\Models\Adquisicion;
use App\Models\Contratista;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use App\Models\ProformaPlano;
use App\Models\MovimientoCaja;
use App\Models\OrdenRecepcion;
use App\Models\DetalleManoObra;
use App\Models\JPLimpieza\Caja;
use App\Models\RubroCronograma;
use App\Models\ReposicionTiempo;
use App\Models\Marketing\Contrato;
use App\Models\ResumenPagoSemanal;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProformaAdecentamiento;
use App\Models\ProgramaArquitectonico;
use App\Exports\ReportSolicitudesExport;
use setasign\Fpdi\PdfParser\StreamReader;
use App\Exports\ReportAdquisicionesExport;
use App\Models\PagoOrdenTrabajoContratista;
use App\Exports\ReportGasolinaCamionetaExport;
use App\Models\JPLimpieza\PlantillaPresupuesto;
use App\Exports\ReporteAdquisicionesOperativoJPExport;
use App\Models\JPLimpieza\ManoObra as JPLimpiezaManoObra;
use App\Models\JPLimpieza\Proyecto as JPLimpiezaProyecto;
use App\Models\JPLimpieza\Adquisicion as JPLimpiezaAdquisicion;

class GenerarPdfController extends Controller
{
    public function generarPdfPedido(Adquisicion $pedido)
    {
        $informacion_empresa = CatalogoDato::getChildrenCatalogo('informacion.general');

        $cliente = [];
        foreach ($informacion_empresa as $info) {
            switch ($info->slug) {
                case 'informacion.general.nombre.empresa':
                    $cliente['nombre'] = $info->detalle;
                    break;
                case 'informacion.general.direccion':
                    $cliente['direccion'] = $info->detalle;
                    break;
                case 'informacion.general.telefono':
                    $cliente['telefono'] = $info->detalle;
                    break;
                case 'informacion.general.correo':
                    $cliente['correo'] = $info->detalle;
                    break;
            }
        }

        $items = [];
        foreach ($pedido->adquisiciones_detalle as $key => $detalle) {
            $items[] = [
                'producto' => $detalle->producto->descripcion,
                'cantidad' => $detalle->cantidad_solicitada,
                'necesidad' => $detalle->necesidad,
                'kilometraje' => $detalle->kilometraje,
            ];
        }

        $orden = [
            'numero_pedido' => 'ORD-' . $pedido->numero,
            'proyecto' => $pedido->proyecto->nombre_proyecto,
            'etapa' => $pedido->etapa->descripcion,
            'tipo' => $pedido->tipo_etapa->descripcion,
            'fecha' => date('d-m-Y', strtotime($pedido->fecha)),
            'cliente' => $cliente,
            'items' => $items,
        ];

        $logo_base64 = $this->logoBase64();
        $pdf = PDF::loadView('pdf.adquiscion', compact('orden', 'logo_base64'));

        return $pdf->stream('orden_pedido_' . $pedido->numero . '.pdf'); // para verl el pdf directamnete (stream), para descargar (download)

    }

    public function generarPdfRecepcion(Adquisicion $pedido)
    {
        $informacion_empresa = CatalogoDato::getChildrenCatalogo('informacion.general');
        $order_recepcion = OrdenRecepcion::where('adquisicion_id', $pedido->id)->first();
        if (!isset($order_recepcion) || !$order_recepcion->completado) {
            return back()->with(
                'toast_error',
                'No se ha completado la orden de recepción, complétela para poder generar el archivo.',
            );
        }
        $cliente = [];
        foreach ($informacion_empresa as $info) {
            switch ($info->slug) {
                case 'informacion.general.nombre.empresa':
                    $cliente['nombre'] = $info->detalle;
                    break;
                case 'informacion.general.direccion':
                    $cliente['direccion'] = $info->detalle;
                    break;
                case 'informacion.general.telefono':
                    $cliente['telefono'] = $info->detalle;
                    break;
                case 'informacion.general.correo':
                    $cliente['correo'] = $info->detalle;
                    break;
            }
        }

        $items = [];
        $total_orden = 0;

        foreach ($pedido->adquisiciones_detalle as $key => $detalle) {

            $iva = $detalle->iva ?? 0;
            $valor_unitario = calcularProcentaje($detalle->valor, $detalle->costo_indirecto ?? 0, 4);
            $total = calcularTotalProducto($detalle->cantidad_solicitada, $valor_unitario, $iva);

            $total_orden += $pedido->estado == 'Completado' ? calcularTotalProducto($detalle->cantidad_solicitada, $valor_unitario, $iva) : $total;

            $items[] = [
                'producto' => $detalle->producto->descripcion,
                'cantidad' => $detalle->cantidad_solicitada,
                'cantidad_recibida' => $detalle->cantidad_recibida,
                'unidad_medida' => $detalle->unidad_medida->descripcion ?? '',
                'valor' => $detalle->valor,
                'iva' => $iva,
                'total' => $total,
                'necesidad' => $detalle->necesidad,
                'kilometraje' => $detalle->kilometraje,
                'costo_indirecto' => $detalle->costo_indirecto ?? 0,
            ];
        }

        $orden = [
            'proyecto' => $pedido->proyecto_id ? $pedido->proyecto->nombre_proyecto : 'General',
            'subproyecto' => $pedido->subproyecto ?? '---',
            'numero_pedido' => $pedido->numero,
            'semana' => $pedido->semanas,
            'fecha' => date('d-m-Y', strtotime($pedido->fecha)),
            'proveedor' => $order_recepcion->proveedor->razon_social,
            'etapa' => $pedido->etapa->descripcion,
            'tipo' => $pedido->tipo_etapa->descripcion,
            'cliente' => $cliente,
            'forma_pago' => $order_recepcion->forma_pago->descripcion,
            'items' => $items,
            'total_orden' => $total_orden,
            'estado_pedido' => $pedido->estado,
            'factura' => $pedido->factura,
        ];

        $logo_base64 = $this->logoBase64();
        //return view('pdf.recepcion', compact('orden', 'logo_base64'));
        $pdf = PDF::loadView('pdf.recepcion', compact('orden', 'logo_base64'));

        return $pdf->stream('orden_recepcion_' . $pedido->numero . '.pdf'); // para verl el pdf directamnete (stream), para descargar (download)
    }

    public function planificacionManoObraPDF(ManoObra $mano_obra, $pago = false)
    {
        // Obtener todos los registros de mano de obra y agruparlos por proveedor y fechas
        $detalles = DetalleManoObra::select('detalle_mano_obra.*') // Selecciona todas las columnas de detalle_mano_obras
            ->join('proveedores', 'detalle_mano_obra.proveedor_id', '=', 'proveedores.id') // Une con la tabla proveedores
            ->where('mano_obra_id', $mano_obra->id) // Filtra por mano_obra_id
            ->orderBy('proveedores.apellidos', 'asc') // Ordena por el nombre del proveedor
            ->orderBy('proveedores.nombres', 'asc') // También puedes ordenar por fecha
            ->with(['proveedor', 'articulo']) // Carga las relaciones para acceso posterior
            ->get();
        $agrupados = $detalles->groupBy('proveedor_id');

        $info_mano_obra = [
            'pago_nro' => '',
            'proyecto' => $mano_obra->proyecto->nombre_proyecto,
            'subproyecto' => $mano_obra->subproyecto ?? '---',
            'etapa' => $mano_obra->etapa->descripcion,
            'actividad' => $mano_obra->actividad->descripcion ?? null,
            'fecha' => dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin),
            'semana' => $mano_obra->semana,
            'detalle' => []  // Para almacenar los detalles procesados
        ];

        if ($pago) {
            $pago_nro = $mano_obra->pago_mano_obra()->first();

            $formateada = Carbon::parse($pago_nro->created_at)->format('Ymd');
            $numero_orden = $formateada . '-' . str_pad($pago_nro->id, 3, '0', STR_PAD_LEFT);

            $detalle = $mano_obra->getDetalleManoObraGroupTrabajador($mano_obra->id, 'completo');
            $info_mano_obra['detalle'] = collect($detalle['detalle'])->all();
            $info_mano_obra['pago_nro'] =  $numero_orden;
        } else {
            foreach ($agrupados as $proveedor_id => $registros_por_proveedor) {
                $nombre_mostrado = false;  // Bandera para saber si ya mostramos el nombre del proveedor

                foreach ($registros_por_proveedor->groupBy('articulo_id') as $articulo_id => $registros) {
                    // Inicializamos las variables para cada trabajador y su cargo
                    $fila = [
                        'nombre' => '',
                        'cargo' => '',
                        'dias' => array_fill(0, 6, 0),   // Días de la semana en blanco (Lunes a Sábado)
                        'total_adicional' => 0,
                        'total' => 0,
                        'total_descuento' => 0,
                        'liquido_recibir' => 0,
                        'observacion' => [],
                        'detalle_adicional' => [],
                        'detalle_descuento' => [],
                    ];

                    // Iteramos los registros de cada proveedor y cargo
                    foreach ($registros as $detalle) {
                        $articulo = $detalle->articulo;
                        $proveedor = $detalle->proveedor;

                        $fila['nombre'] = strtoupper($proveedor->apellidos . ' ' . $proveedor->nombres);
                        // El cargo puede cambiar por artículo
                        $fila['cargo'] = $articulo->descripcion;

                        // Convertimos la fecha a día de la semana (1 = Lunes, 2 = Martes, etc.)
                        $diaSemana = Carbon::parse($detalle->fecha)->dayOfWeek;  // 0 = Domingo, 1 = Lunes, etc.

                        // Si el día de la semana está entre Lunes y Sábado
                        if ($diaSemana >= 1 && $diaSemana <= 6) {
                            // Restamos 1 a `diaSemana` para ajustar al índice (Lunes = 0, Sábado = 5)
                            $fila['dias'][$diaSemana - 1] += $detalle->valor;
                        }

                        // Acumulamos los totales
                        $fila['total'] += $detalle->valor + $detalle->adicional;
                        $fila['total_adicional'] += $detalle->adicional;
                        $fila['total_descuento'] += $detalle->descuento;
                        if ($detalle->detalle_adicional) {
                            $fila['detalle_adicional'][] = $detalle->detalle_adicional;
                        }
                        if ($detalle->detalle_descuento) {
                            $fila['detalle_descuento'][] = $detalle->detalle_descuento;
                        }



                        // Si existe una observación, la agregamos
                        if (!empty($detalle->observacion)) {
                            $fila['observacion'][] = $detalle->observacion;  // Concatenamos las observaciones
                        }
                    }

                    // Calculamos el líquido a recibir
                    $fila['liquido_recibir'] = ($fila['total_adicional'] + array_sum($fila['dias'])) - $fila['total_descuento'];

                    // Añadimos la fila al array de resultados
                    $info_mano_obra['detalle'][] = $fila;

                    // Para las siguientes filas del mismo proveedor, dejamos el nombre en blanco
                    $nombre_mostrado = true;
                }
            }
        }

        $logo_base64 = $this->logoBase64();

        $pdf = PDF::loadView('pdf.mano_obra', compact('info_mano_obra', 'logo_base64', 'pago'))->setPaper('a3', 'landscape');
        return $pdf->stream('reporte_mano_obra.pdf');
    }

    public function ordenTrabajoContratistaPDF(Contratista $orden_trabajo)
    {
        $logo_base64 = $this->logoBase64();

        $pagos = $orden_trabajo->pagosOrdenTrabajoContratista;
        if ($pagos->count() > 0) {
            $primer_pago = $pagos->first();
            $fecha_pago = $primer_pago->fecha;
            $fecha_final = dateFormatHumans(calcularFechaFinal($fecha_pago, $orden_trabajo->plazo_semanas));
        } else {
            $fecha_final = 'No se ha realizado anticipo';
        }


        $info = [
            'orden' => numeroOrden($orden_trabajo, false),
            'proyecto' => strtoupper($orden_trabajo->proyecto->nombre_proyecto),
            'subproyecto' => $orden_trabajo->subproyecto ?? '---',
            'etapa' => $orden_trabajo->etapa->descripcion,
            'fecha' => dateFormatHumans($orden_trabajo->fecha),
            'plazo' => $fecha_final,
            'contratista' => strtoupper($orden_trabajo->proveedor->razon_social),
            'categoria' => strtoupper($orden_trabajo->articulo->descripcion),
            'valor_contratado' => number_format($orden_trabajo->total_contratistas, 4),
            'avance' => number_format($orden_trabajo->pagos_contratistas, 4),
            'saldo' => number_format(($orden_trabajo->total_contratistas - $orden_trabajo->pagos_contratistas), 4),
            'estado' => $orden_trabajo->tipo_pago_contratista ? ($orden_trabajo->tipo_pago_contratista == "Completado" ? "Completado" : "En Proceso") : 'NUEVO',
            'detalle' => []
        ];

        foreach ($orden_trabajo->detalle_contratistas as $detalle) {
            $info['detalle'][] = [
                'producto' => $detalle->articulo->descripcion,
                'cantidad' => $detalle->cantidad,
                'unidad_medida' => $detalle->unidad_medida->descripcion,
                'valor_unitario' => number_format($detalle->valor_unitario, 4),
                'costo_indirecto' => $detalle->costo_indirecto ?? 0,
                'total' => number_format(($detalle->cantidad * $detalle->valor_unitario), 4)
            ];
        }


        //return $info;
        $pdf = PDF::loadView('pdf.orden_trabajo', compact('info', 'logo_base64'));
        return $pdf->stream('orden_trabajo.pdf');
    }


    public function exportarPresupuestoPDFD(Proyecto $proyecto)
    {
        $categorias = Proyecto::presupuestoValorado($proyecto->id);
        $pdf = PDF::loadView('pdf.presupuesto_referencial', compact('categorias', 'proyecto'));
        return $pdf->stream('presupuesto_referencial.pdf');
    }

    public function exportarCronogramaToPDF(Proyecto $proyecto)
    {
        $categorias = $proyecto->presupuestoValorado($proyecto->id);
        $plazo_semanas = plazoSemanasProyecto($proyecto->fecha_inicio, $proyecto->fecha_finalizacion);
        $plazo_meses = calcularMesesEntreFechas($proyecto->fecha_inicio, $proyecto->fecha_finalizacion);

        $cronogramas = Cronograma::with('rubro_cronograma')
            ->where('proyecto_id', $proyecto->id)
            ->orderBy('id', 'asc')
            ->get();

        // Agrupar los datos por rubro_cronograma_id
        $cronograma = $cronogramas->groupBy('rubro_cronograma_id')->map(function ($grupo) {
            // Obtener el nombre del rubro
            $rubroCronogramaNombre = $grupo->first()->rubro_cronograma->descripcion;
            // Obtener el id del rubro_cronograma
            $rubro_cronograma_id = $grupo->first()->rubro_cronograma->id;

            // Crear un array con las semanas donde el rubro está presente
            // Organizar las semanas con sus días correspondientes
            $semanas = $grupo->groupBy('semana')->map(function ($semanaGrupo) {
                return $semanaGrupo->pluck('dia')->toArray();
            });

            return [
                'rubro_cronograma_id' => $rubro_cronograma_id,
                'rubro_cronograma_nombre' => $rubroCronogramaNombre,
                'semanas' => $semanas,
            ];
        });

        //return $cronograma;

        $rubros_cronograma = RubroCronograma::where('activo', true)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

        $total_estructural = $categorias->sum(function ($categoria) {
            return $categoria->rubrosPresupuesto->sum(function ($rubro) {
                return $rubro->presupuestoProyectos
                    ->filter(function ($proyecto) {
                        // Filtrar proyectos basados en la relación etapa_construccion
                        return $proyecto->etapa_construccion &&
                            $proyecto->etapa_construccion->slug ===
                            'etapas.construccion.estructural';
                    })
                    ->sum(function ($proyecto) {
                        // Calcular cantidad * valor_unitario
                        return $proyecto->cantidad * $proyecto->valor_unitario;
                    });
            });
        });

        $total_mpel = $categorias->sum(function ($categoria) {
            return $categoria->rubrosPresupuesto->sum(function ($rubro) {
                return $rubro->presupuestoProyectos
                    ->filter(function ($proyecto) {
                        // Filtrar proyectos basados en la relación etapa_construccion
                        return $proyecto->etapa_construccion &&
                            ($proyecto->etapa_construccion->slug ===
                                'etapas.construccion.mamposteria' ||
                                $proyecto->etapa_construccion->slug ===
                                'etapas.construccion.enlucidos');
                    })
                    ->sum(function ($proyecto) {
                        // Calcular cantidad * valor_unitario
                        return $proyecto->cantidad * $proyecto->valor_unitario;
                    });
            });
        });

        $total_acabados = $categorias->sum(function ($categoria) {
            return $categoria->rubrosPresupuesto->sum(function ($rubro) {
                return $rubro->presupuestoProyectos
                    ->filter(function ($proyecto) {
                        // Filtrar proyectos basados en la relación etapa_construccion
                        return $proyecto->etapa_construccion &&
                            $proyecto->etapa_construccion->slug ===
                            'etapas.construccion.acabados';
                    })
                    ->sum(function ($proyecto) {
                        // Calcular cantidad * valor_unitario
                        return $proyecto->cantidad * $proyecto->valor_unitario;
                    });
            });
        });

        $total = $total_estructural + $total_mpel + $total_acabados;

        $pdf = PDF::loadView('pdf.cronograma', compact('proyecto', 'categorias', 'plazo_semanas', 'plazo_meses', 'cronograma', 'rubros_cronograma', 'total_estructural', 'total_mpel', 'total_acabados', 'total'))->setPaper('a3', 'landscape');
        return $pdf->stream('cronograma.pdf');
    }

    public function exportarActividadesDiasCronogramaToPDF(Proyecto $proyecto, $semana)
    {
        $cronogramas = Cronograma::with('rubro_cronograma')
            ->where('proyecto_id', $proyecto->id)
            ->where('semana', $semana)
            ->get();

        // Agrupar los datos por rubro_cronograma_id
        // Agrupar los datos por rubro_cronograma_id
        $actividades = $cronogramas->groupBy('rubro_cronograma_id')->map(function ($grupo) {
            return [
                'rubro_cronograma_id' => $grupo->first()->rubro_cronograma->id,
                'rubro_cronograma_nombre' => $grupo->first()->rubro_cronograma->descripcion,
                'dias' => $grupo->mapWithKeys(function ($item) {
                    return [$item->dia => ['observacion' => $item->observacion, 'id' => $item->id]];
                })->toArray(),
            ];
        });

        // Extraer todos los días únicos (lunes a domingo)
        $diasSemana = config('app.diasSemana', []);

        // Estructurar los datos para la tabla
        $tablaDatos = [];
        foreach ($actividades as $actividad) {
            foreach ($diasSemana as $dia) {
                $tablaDatos[$actividad['rubro_cronograma_nombre']][$dia] = $actividad['dias'][$dia] ?? ['observacion' => '', 'id' => null];
            }
        }


        $fecha_semanas = fechasSemana($proyecto->fecha_inicio, $proyecto->fecha_finalizacion);

        $info_cronograma_dias = [
            'proyecto' => $proyecto->nombre_proyecto,
            'semana' => $semana,
            'fecha_semana' => $fecha_semanas[$semana - 1],
            'tablaDatos' => $tablaDatos,
            'diasSemana' => $diasSemana,
        ];

        $pdf = PDF::loadView('pdf.cronograma_dias', compact('info_cronograma_dias'))->setPaper('a3', 'landscape');
        return $pdf->stream('actividades_dias.pdf');
    }

    public function pdfResumenPagoSemanal(ResumenPagoSemanal $resumen)
    {
        $pdf = PDF::loadView('pdf.resuemen_pago_semanal', compact('resumen'));
        return $pdf->stream('resuemen_pago_semanal.pdf');
    }

    /**
     * Reporte de adquisiciones 
     * Materiales y Herramientas / Servicios
     */
    public function reportAdquisiciones(Request $request, $tipo_reporte)
    {
        $ordenado = $request->input('ordenado'); // Ejemplo: "secuencial"
        $fechas = $request->input('fechas'); // Ejemplo: "03/12/2025 - 03/30/2025"
        $estado = $request->input('estado'); // Ejemplo: null o "Completado"
        $proyecto = $request->input('proyecto'); // Ejemplo: null o ID del proyecto
        $etapa = $request->input('etapa'); // Ejemplo: null o ID de la etapa
        $tipo = $request->input('tipo'); // Ejemplo: null o tipo_etapa_id
        $tipo_reporte = $request->input('tipo_reporte'); // Ejemplo: null o tipo_etapa_id
        $necesidad = $request->input('necesidad'); // Ejemplo: null o valor de necesidad
        $costo = $request->input('costo'); // Ejemplo: null o valor de costo
        $cargo = $request->input('cargo'); // Ejemplo: null o ID del cargo
        $proveedor = $request->input('proveedor'); // Ejemplo: null o ID del proveedor
        $producto = $request->input('producto'); // Ejemplo: null o ID del producto

        $tipoAdquisisicon = CatalogoDato::find($tipo);
        if ($tipo_reporte == 'global') {
            $total_general = $request->input('total_general', 0);
            $costos_indirecto = calcularProcentaje($total_general, $request->input('costos_indirecto', 0), 4);
            $data = Adquisicion::reporteGlobalAdquisiciones($request);
            $view = 'reporte_adquisiciones_global';

            $pdf = PDF::loadView('pdf.' . $view, compact('data', 'costos_indirecto', 'total_general'))->setPaper('a4', 'landscape');
        } elseif ($tipo_reporte == 'comparativo') { //* Reporte comparativo de adquisiciones//
            $data = Adquisicion::reporteComparativoAdquisiciones($request);
            $view = 'reporte_adquisiciones_comparativo';
            $pdf = PDF::loadView('pdf.' . $view, compact('data'))->setPaper('a4', 'landscape');
        } else {
            if ($tipoAdquisisicon->slug == 'meteriales.herramientas' || $tipoAdquisisicon->slug == 'servicios') {
                $query = Adquisicion::dataReporteAdquisiciones($request);
                $view = 'reporte_adquisiciones';
            } elseif ($tipoAdquisisicon->slug == 'contratista') {
                $query = Contratista::filtroContratista($request);
                $view = 'reporte_contratista';
            } else {
                $query = ManoObra::filtroManoObra($request);
                $view = 'reporte_mano_obra';
            }

            $tipo = CatalogoDato::find($tipo);

            $totalGeneral = $query->map(function ($item) {
                // Verificar si 'total' existe antes de usarlo
                $total = isset($item['total']) ? $item['total'] : 0;
                return floatval(str_replace(['$', ','], '', $total));
            })->sum() ?? 0;

            $totalPagado = $query->map(function ($item) {
                $totalPagado = isset($item['total_pagado']) ? $item['total_pagado'] : 0;
                return floatval(str_replace(['$', ','], '', $totalPagado));
            })->sum() ?? 0;

            $totalSaldos = $query->map(function ($item) {
                $saldo = isset($item['saldo']) ? $item['saldo'] : 0;
                return floatval(str_replace(['$', ','], '', $saldo));
            })->sum() ?? 0;

            $totalCantidades = $query->map(function ($item) {
                // Verificar si 'cantidad' existe antes de usarlo
                $total = isset($item['cantidad']) ? $item['cantidad'] : 0;
                return $total;
            })->sum() ?? 0;

            $producto = $producto != '' ? Articulo::find($producto) : '';
            $proveedor = $proveedor != '' ? Proveedor::find($proveedor) : '';
            $cargo = $cargo != '' ? Articulo::find($cargo) : '';


            if ($tipo_reporte === 'excel') {
                return Excel::download(new ReportAdquisicionesExport($query, $tipo, $producto, $proveedor, $fechas, $cargo, $totalGeneral, $totalPagado, $totalSaldos, $view), $view . '.xlsx');
            }

            $pdf = PDF::loadView('pdf.' . $view, compact('query', 'tipo', 'producto', 'proveedor', 'fechas', 'cargo', 'totalGeneral', 'totalPagado', 'totalSaldos', 'totalCantidades'))->setPaper('a3', 'landscape');
        }
        return $pdf->stream('reportes.pdf');
    }

    public function reportGaolinaCamioneta(Request $request, $tipo_reporte)
    {
        $fechas = $request->input('fechas');
        $query = Adquisicion::dataReporteAdquisiciones($request, true);
        $result = new \Illuminate\Support\Collection();
        $fecha_inicio = '';
        $km_anterior = 0;

        foreach ($query as $index => $item) {
            $galones = $item['adquisicion']->adquisiciones_detalle->first()->cantidad_solicitada;
            $km_carga = $item['adquisicion']->adquisiciones_detalle->first()->kilometraje;
            $km_recorrido = $km_anterior > 0 ? $km_carga - $km_anterior : 0;
            $km_galon = $km_recorrido / $galones;
            $valor = $item['adquisicion']->adquisiciones_detalle->first()->valor ?? 0;

            if ($valor > 0) {
                $iva = $item['adquisicion']->adquisiciones_detalle->first()->iva ?? 0;
                $valor = calcularTotalProducto($galones, $valor, $iva);
            }

            $result->add([
                'fecha' => Carbon::createFromFormat('Y-m-d', $item['adquisicion']->fecha)->format('d-m-Y'),
                'dias' => $index > 0 ? diasEntreFechas($fecha_inicio, $item['adquisicion']->fecha) : 0,
                'km_carga' => $km_carga,
                'km_anterior' => $km_anterior,
                'km_recorrido' => $km_recorrido,
                'km_galon' => $km_galon,
                'valor' => '$ ' . number_format($valor, 4),
                'galones' => $galones,
            ]);

            $fecha_inicio = $item['adquisicion']->fecha;
            $km_anterior = $item['adquisicion']->adquisiciones_detalle->first()->kilometraje;
        }

        if ($tipo_reporte === 'excel') {
            return Excel::download(new ReportGasolinaCamionetaExport($result, $fechas), 'reporte_gasolina_camioneta.xlsx');
        }

        $pdf = PDF::loadView('pdf.reporte_gasolina', compact('result', 'fechas'))->setPaper('a4', 'landscape');
        return $pdf->stream('reportes.pdf');
    }

    public function reportSolicitudes(Request $request, $tipo_reporte)
    {
        $fechas = $request->input('fechas');
        $tipo_solicitud = $request->input('tipo_solicitud');

        if ($tipo_solicitud != 'reposiciones_global' && $tipo_solicitud != 'reposiciones_detallado') {
            $query = Solicitud::dataReporteSolicitudes($request);
            $tipo_solicitud = CatalogoDato::find($tipo_solicitud)->descripcion;
        } else {
            $query = ReposicionTiempo::getTotalReposiciones($request);
        }

        if ($tipo_reporte === 'excel') {
            return Excel::download(new ReportSolicitudesExport($query, $fechas, $tipo_solicitud), 'reporte_solicitudes.xlsx');
        }
        $pdf = PDF::loadView('pdf.reporte_solicitudes', compact('query', 'fechas', 'tipo_solicitud'))->setPaper('a4', 'landscape');
        return $pdf->stream('reportes.pdf');
    }

    //** Reporte de caja */
    public function reportCaja(Request $request)
    {
        $query = MovimientoCaja::dataReporteCaja($request);
        $tipo = 'prime';
        $pdf = PDF::loadView('pdf.reporte_caja', compact('query', 'tipo'))->setPaper('a4', 'landscape');
        return $pdf->stream('reportes.pdf');
    }

    public function exportarTareas(Request $request)
    {
        $infoTarea = Tarea::filtroTareas($request);
        $infoTarea = $infoTarea->groupBy('estado.descripcion');

        $pdf = PDF::loadView('pdf.tareas', compact('infoTarea')); //->setPaper('a3', 'landscape');
        return $pdf->stream('tareas.pdf');
    }

    public function pagoOrdenTrabajoPDF(PagoOrdenTrabajoContratista $pago)
    {
        $pdf = PDF::loadView('pdf.orden_pago_contratista', compact('pago'));
        return $pdf->stream('orden_pago_contratista.pdf');
    }

    //** Presupuesto JPLimpieza */
    public function presupuestoJPLimpiezaPDF(JPLimpiezaProyecto $proyecto)
    {
        $categorias = PlantillaPresupuesto::with([
            'hijos',
            // Aquí está la magia: añadimos una condición a la relación anidada
            'hijos.presupuestoProyecto' => function ($query) use ($proyecto) {
                // Le decimos que solo traiga el presupuesto que pertenezca al proyecto actual
                $query->where('proyecto_id', $proyecto->id);
            }
        ])
            ->whereNull('padre_id')
            ->where('activo', true)
            ->get();

        $pdf = PDF::loadView('pdf.presupuesto_jplimpieza', compact('proyecto', 'categorias'))->setPaper('a4', 'landscape');
        return $pdf->stream('tareas.pdf');
    }

    //** Mano obra JPLimpieza */
    public function manoObraJPLimpiezaPDF(JPLimpiezaManoObra $mano_obra)
    {
        $pdf = PDF::loadView('pdf.mano_obra_jplimpieza', compact('mano_obra'))->setPaper('a3', 'landscape');
        return $pdf->stream('mano_obra.pdf');
    }

    //** Adquisiciones */
    public function adquisicionJPLimpiezaPDF(JPLimpiezaAdquisicion $adquisicion)
    {
        $pdf = PDF::loadView('pdf.adquisicion_jplimpieza', compact('adquisicion'))->setPaper('a4', 'landscape');
        return $pdf->stream('adquisicion.pdf');
    }

    private function logoBase64()
    {
        $logo_base64 = base64_encode(file_get_contents(public_path('images/logo_empresa.jpg')));
        return $logo_base64;
    }

    //** Reporte de caja JPLimpieza */
    public function reportCajaJPLimpieza(Request $request)
    {
        $query = Caja::dataReporteCaja($request);
        $tipo = 'jp_limpieza';
        $pdf = PDF::loadView('pdf.reporte_caja', compact('query', 'tipo'))->setPaper('a4', 'landscape');
        return $pdf->stream('reportes.pdf');
    }


    //** Proformas */
    public function  proformas($tipo, $id)
    {
        // Cargar la proforma con sus relaciones. Eager loading es más eficiente.
        if ($tipo == 'adecentamientos') {
            $proforma = ProformaAdecentamiento::find($id);
        } else {
            // Asumimos que ProformaPlano tiene una relación hasOne con ProgramaArquitectonico
            $proforma = ProformaPlano::with('programaArquitectonico.espacios.categoria')->find($id);
        }

        $empresa = getInfoEmpresa();

        if (!$proforma) {
            return back()->with('error', 'No se encontró la proforma solicitada.');
        }

        // --- LÓGICA PARA INCLUIR EL PROGRAMA ARQUITECTÓNICO ---
        $programaData = null; // Por defecto, no hay datos del programa

        // Si la proforma es de tipo "planos" y tiene un programa asociado
        if ($tipo != 'adecentamientos' && $proforma->programaArquitectonico) {
            $programa = $proforma->programaArquitectonico;

            $groupedEspacios = $programa->espacios
                ->sortBy('categoria.orden')
                ->groupBy('categoria.nombre');

            $total_m2_interiores = $programa->espacios->where('categoria.es_exterior', false)->sum('m2');
            $total_m2_exteriores = $programa->espacios->where('categoria.es_exterior', true)->sum('m2');

            // Empaquetamos todos los datos del programa en un solo array
            $programaData = [
                'programa' => $programa,
                'groupedEspacios' => $groupedEspacios,
                'total_m2_interiores' => $total_m2_interiores,
                'total_m2_exteriores' => $total_m2_exteriores,
            ];
        }

        // 1. Generar el primer PDF (portrait)
        $pdfPortrait = PDF::loadView('pdf.proforma', [
            'proforma' => $proforma,
            'empresa' => $empresa,
            'tipo' => $tipo,
            'programaData' => null // Solo la primera parte
        ])->setPaper('a4', 'portrait')->output();

        // 2. Generar el segundo PDF (landscape) solo si hay programaData
        $pdfLandscape = null;
        if ($programaData) {
            $pdfLandscape = PDF::loadView('pdf.programa_arquitectonico', $programaData)
                ->setPaper('a4', 'landscape')
                ->output();
        }

        // 3. Unir ambos PDFs usando FPDI
        $pdf = new Fpdi();

        // Agregar el PDF portrait
        $pageCount = $pdf->setSourceFile(StreamReader::createByString($pdfPortrait));
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplIdx = $pdf->importPage($pageNo);
            $pdf->AddPage('P');
            $pdf->useTemplate($tplIdx);
        }

        // Agregar el PDF landscape si existe
        if ($pdfLandscape) {
            $pageCount = $pdf->setSourceFile(StreamReader::createByString($pdfLandscape));
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
                $pdf->AddPage('L');
                $pdf->useTemplate($tplIdx);
            }
        }

        $nombreArchivo = 'proforma_' . $tipo . '_' . $proforma->numero . '.pdf';

        $pdf->SetTitle('Proforma ' . $tipo . ' ' . $proforma->numero);
        // 4. Salida del PDF unido
        return response($pdf->Output('S', $nombreArchivo))
            ->header('Content-Type', 'application/pdf');
    }

    public function programaArquitectonicoPDF(ProgramaArquitectonico $programa)
    {
        $empresa = getInfoEmpresa();

        $groupedEspacios = $programa->espacios()
            ->with('categoria')
            ->get()
            ->sortBy('categoria.orden')
            ->groupBy('categoria.nombre');

        $total_m2_interiores = $programa->espacios->where('categoria.es_exterior', false)->sum('m2');
        $total_m2_exteriores = $programa->espacios->where('categoria.es_exterior', true)->sum('m2');

        // Cargar la vista específica para PDF con los datos
        $pdf = PDF::loadView('pdf.programa_arquitectonico', compact(
            'programa',
            'empresa',
            'groupedEspacios',
            'total_m2_interiores',
            'total_m2_exteriores'
        ));

        // Opcional: Configurar el tamaño y la orientación del papel
        // 'landscape' es 'apaisado', ideal para tablas anchas.
        $pdf->setPaper('a4', 'landscape');

        // Devolver el PDF al navegador
        return $pdf->stream('programa_arquitectonico_' . $programa->proforma->numero . '.pdf');
    }

    public function exportarReporteJPLimpieza(Request $request, $export)
    {

        $tipo_reporte = $request->tipo_reporte;
        if ($tipo_reporte == 'operativo') {
            return $this->reporteAdquisicionesOperativo($request, $export);
        } elseif ($tipo_reporte == 'global') {
            return $this->reporteAdquisicionesGlobal($request, $export);
        } elseif ($tipo_reporte == 'balance') {
            return $this->reporteAdquisicionesFinanciero($request, $export);
        }
    }

    private function reporteAdquisicionesOperativo($request, $export, $tipo = 'operativo')
    {
        $reportData = JPLimpiezaAdquisicion::dataReporteAdquisiciones($request);
        $proyecto = JPLimpiezaProyecto::find($request->proyecto);

        $fechaReporte = now()->format('d/m/Y');

        if ($request->filled('fechas')) {
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicio = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFin = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');

            $fechaReporte = $inicio . ' hasta ' . $fin;
        } else {
            $fechaInicio = null;
            $fechaFin = null;
        }
        // Calcula el gran total
        $grandTotal = 0;
        foreach ($reportData as $categoria => $items) {
            if ($categoria === 'Mano de Obra') {
                // Asegúrate de que el campo 'costo' existe y es numérico
                $grandTotal += collect($items)->sum('total_recibir');
            } elseif ($categoria === 'Contratistas') {
                // Asegúrate de que el campo 'monto total' existe y es numérico
                $grandTotal += collect($items)->sum('total_contratado');
            } else {
                // Para Adquisiciones, asumiendo que tienes un campo numérico como 'total_general'
                // ¡IMPORTANTE! No sumes el campo formateado ('total_general_formatted')
                $grandTotal += collect($items)->sum('total_general_formatted');
            }
        }

        if ($export == 'pdf') {
            $pdf = PDF::loadView('pdf.jp_limpieza.adquisiciones', compact('reportData', 'grandTotal', 'tipo', 'proyecto', 'fechaReporte'))->setPaper('a4', 'landscape');
            return $pdf->stream('reportes.pdf');
        } elseif ($export == 'excel') {
            $fileName = 'reporte-adquisiciones-' . now()->format('Y-m-d') . '.xlsx';
            return Excel::download(new ReporteAdquisicionesOperativoJPExport($reportData, $grandTotal), $fileName);
        }
    }

    private function reporteAdquisicionesGlobal($request, $export, $tipo = 'global')
    {
        $reportData = JPLimpiezaAdquisicion::dataReportePorArticulo($request);
        $grandTotal = collect($reportData)->flatten(1)->sum('monto_total');
        $proyecto = JPLimpiezaProyecto::find($request->proyecto);
        $fechaReporte = now()->format('d/m/Y');

        if ($request->filled('fechas')) {
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicio = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFin = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');

            $fechaReporte = $inicio . ' hasta ' . $fin;
        } else {
            $fechaInicio = null;
            $fechaFin = null;
        }

        if ($export == 'pdf') {
            $pdf = PDF::loadView('pdf.jp_limpieza.adquisiciones', compact('reportData', 'grandTotal', 'tipo', 'proyecto', 'fechaReporte'))->setPaper('a4', 'landscape');
            return $pdf->stream('reportes.pdf');
        }
    }

    private function reporteAdquisicionesFinanciero($request, $export, $tipo = 'balance')
    {
        $reportData = JPLimpiezaProyecto::dataReporteBalance($request);
        $totalIngresosGeneral = $reportData->sum('total_ingresos');
        $totalGastosGeneral = $reportData->sum('total_gastos');
        $utilidadGeneral = $reportData->sum('utilidad');

        $proyecto = JPLimpiezaProyecto::find($request->proyecto);
        $fechaReporte = now()->format('d/m/Y');

        if ($request->filled('fechas')) {
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicio = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFin = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');

            $fechaReporte = $inicio . ' hasta ' . $fin;
        } else {
            $fechaInicio = null;
            $fechaFin = null;
        }


        if ($export == 'pdf') {
            $pdf = PDF::loadView('pdf.jp_limpieza.adquisiciones', compact('reportData', 'totalIngresosGeneral', 'totalGastosGeneral', 'utilidadGeneral', 'tipo', 'proyecto', 'fechaReporte'))->setPaper('a4', 'landscape');
            return $pdf->stream('reportes.pdf');
        }
    }

    public function previewContratoReserva(Contrato $contrato)
    {
        $cliente = $contrato->procesoVenta->cliente;
        $htmlDelContrato = $contrato->contenido;
        $pdf = PDF::loadHTML($htmlDelContrato)->setPaper('a4', 'portrait');
        return $pdf->stream('contrato_reserva.pdf');
    }
}