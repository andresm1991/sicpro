<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        // 1. Recopilar y validar filtros de entrada
        $filters = self::prepareFilters($request);

        // Si no hay un proyecto, no podemos continuar.
        if (!$filters['proyectoModel']) {
            return ['data' => [], 'subproyecto' => $filters['subproyecto']];
        }

        $resultado = [];

        // 2. Obtener datos para cada categoría (si el filtro 'tipo' lo permite)
        // Cada función ahora devolverá datos ya agrupados por nombre de etapa.
        if (in_array($filters['tipo_slug'], [null, 'contratista'])) {
            $contratistas = self::getContratistasData($filters);
            self::mergeResults($resultado, $contratistas, 'contratista');
        }
        if (in_array($filters['tipo_slug'], [null, 'mano.obra'])) {
            $manosDeObra = self::getManoDeObraData($filters);
            self::mergeResults($resultado, $manosDeObra, 'mano_obra');
        }
        if (in_array($filters['tipo_slug'], [null, 'meteriales.herramientas', 'servicios'])) {
            $adquisiciones = self::getAdquisicionesData($filters);
            self::mergeResults($resultado, $adquisiciones, 'adquisiciones');
        }

        // 3. Ordenar el resultado final
        $resultado = self::sortFinalResult($resultado);

        return [
            'proyecto' => $filters['proyectoNombre'],
            'subproyecto' => $filters['subproyecto'],
            'data' => $resultado
        ];
    }

    /**
     * Función auxiliar para fusionar los resultados de cada categoría en el array principal.
     */
    private static function mergeResults(array &$resultado, array $dataToMerge, string $category)
    {
        foreach ($dataToMerge as $etapaNombre => $items) {
            // Inicializa la estructura de la etapa si no existe
            if (!isset($resultado[$etapaNombre])) {
                $resultado[$etapaNombre] = [
                    'contratista' => [],
                    'mano_obra' => [],
                    'materiales_herramientas' => [],
                    'servicios' => [],
                ];
            }

            if ($category === 'adquisiciones') {
                // Las adquisiciones se dividen en dos subcategorías
                $resultado[$etapaNombre]['materiales_herramientas'] = array_merge($resultado[$etapaNombre]['materiales_herramientas'], $items['meteriales.herramientas'] ?? []);
                $resultado[$etapaNombre]['servicios'] = array_merge($resultado[$etapaNombre]['servicios'], $items['servicios'] ?? []);
            } else {
                $resultado[$etapaNombre][$category] = array_merge($resultado[$etapaNombre][$category], $items);
            }
        }
    }

    /**
     * Prepara y centraliza todos los filtros del request.
     */
    private static function prepareFilters($request): array
    {
        $proyectoId = $request->input('proyecto');
        $proyectoModel = $proyectoId ? Proyecto::find($proyectoId) : null;
        $tipoId = $request->input('tipo');

        return [
            'ordenado' => $request->input('ordenado'),
            'fechas' => $request->input('fechas'),
            'estado' => $request->input('estado'),
            'proyecto' => $proyectoId,
            'proyectoModel' => $proyectoModel,
            'proyectoNombre' => $proyectoModel->nombre_proyecto ?? 'Sin proyecto',
            'etapa' => $request->input('etapa'),
            'tipo' => $tipoId,
            'tipo_slug' => $tipoId ? CatalogoDato::find($tipoId)->slug : null,
            'costo_directo' => $request->input('costo'), // Asumiendo que 'costo' es un ID de etapa para costos directos/indirectos
            'proveedor' => $request->input('proveedor'),
            'producto' => $request->input('producto'),
            'necesidad' => $request->input('necesidad'),
            'forma_pago' => $request->input('forma_pago'),
            'subproyecto' => $request->input('subproyecto'),
        ];
    }

    /**
     * Obtiene y procesa los datos de Contratistas, agrupados por etapa.
     * La lógica de cálculo es compleja, por lo que agrupamos en PHP después de filtrar en la DB.
     */
    private static function getContratistasData(array $filters): array
    {
        $query = \App\Models\Contratista::query()
            ->select('contratistas.*', 'etapa_catalogo.descripcion as etapa_nombre')
            ->join('catalogo_datos as etapa_catalogo', 'contratistas.etapa_id', '=', 'etapa_catalogo.id')
            ->with(['proveedor:id,razon_social', 'articulo:id,descripcion', 'detalle_contratistas', 'pagosOrdenTrabajoContratista'])
            ->where('contratistas.proyecto_id', $filters['proyecto']);

        self::applyCommonFilters($query, $filters, ['subproyecto', 'proveedor', 'etapa'], 'contratistas');

        $contratistas = $query->get();

        // CAMBIO: Se reemplaza el groupBy por una función de callback para evitar la ambigüedad con SQL.
        // Esto asegura que la agrupación se realice en la colección de PHP.
        return $contratistas->groupBy('etapa_nombre')
            ->map(function ($contratistasPorEtapa) {
                return $contratistasPorEtapa->groupBy(function ($item) {
                    return ($item->proveedor->razon_social ?? 'Sin proveedor') . '|' . ($item->articulo->descripcion ?? 'Sin categoría');
                })->map(function ($group) {
                    $first = $group->first();
                    $totalContratado = $group->sum(function ($c) {
                        return $c->detalle_contratistas->sum(fn($d) => ($d->cantidad ?? 0) * ($d->valor_unitario ?? 0)) * ($c->numero_casas ?? 1);
                    });
                    $pagos = $group->sum(fn($c) => $c->pagosOrdenTrabajoContratista->where('pagado', true)->sum('valor'));

                    return [
                        'proveedor' => $first->proveedor->razon_social ?? 'Sin proveedor',
                        'categoria' => $first->articulo->descripcion ?? 'Sin categoría',
                        'cantidad' => $group->sum('numero_casas'),
                        'total_contratado' => $totalContratado,
                        'pagos' => $pagos,
                        'saldo' => $totalContratado - $pagos,
                    ];
                })->values()->all();
            })->all();
    }

    /**
     * Obtiene y procesa los datos de Mano de Obra, agrupados por etapa.
     */
    private static function getManoDeObraData(array $filters): array
    {
        $query = \App\Models\ManoObra::query()
            ->select(
                'etapa_catalogo.descripcion as etapa_nombre',
                DB::raw('COUNT(DISTINCT mano_obra.fecha_inicio, mano_obra.fecha_fin) as cantidad'),
                DB::raw('SUM(COALESCE(dmo.valor, 0) + COALESCE(dmo.adicional, 0) - COALESCE(dmo.descuento, 0)) as total')
            )
            ->join('catalogo_datos as etapa_catalogo', 'mano_obra.etapa_id', '=', 'etapa_catalogo.id')
            ->join('detalle_mano_obra as dmo', 'mano_obra.id', '=', 'dmo.mano_obra_id')
            ->where('mano_obra.proyecto_id', $filters['proyecto'])
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('pagos_mano_obra')->whereColumn('pagos_mano_obra.mano_obra_id', 'mano_obra.id');
            });

        self::applyCommonFilters($query, $filters, ['subproyecto', 'proveedor', 'etapa'], 'mano_obra');

        $query->groupBy('etapa_catalogo.descripcion');

        $resultados = $query->get();

        // Reformatear para la estructura final: ['Nombre Etapa' => [[...data...]]]
        return $resultados->keyBy('etapa_nombre')->map(function ($item) {
            return [[
                'cantidad' => $item->cantidad,
                'total' => $item->total,
            ]];
        })->all();
    }

    /**
     * Obtiene y procesa los datos de Adquisiciones, agrupados por etapa y tipo de adquisición.
     */
    private static function getAdquisicionesData(array $filters): array
    {
        $query = \App\Models\AdquisicionDetalle::query()
            ->select(
                'etapa_catalogo.descripcion as etapa_nombre',
                'tipo_etapa_catalogo.slug as tipo_etapa_slug',
                'adquisiciones_detalle.articulo_id',
                'articulos.descripcion as articulo_nombre',
                // CAMBIO: Se usa el nuevo alias para la unidad de medida
                'unidad_medida_catalogo.descripcion as unidad_medida_nombre',
                DB::raw('SUM(adquisiciones_detalle.cantidad_solicitada) as cantidad_total'),
                DB::raw('SUM( (adquisiciones_detalle.cantidad_solicitada * adquisiciones_detalle.valor) * (1 + adquisiciones_detalle.iva/100) ) as total_con_iva')
            )
            ->join('adquisiciones', 'adquisiciones_detalle.adquisicion_id', '=', 'adquisiciones.id')
            ->join('catalogo_datos as etapa_catalogo', 'adquisiciones.etapa_id', '=', 'etapa_catalogo.id')
            ->join('articulos', 'adquisiciones_detalle.articulo_id', '=', 'articulos.id')
            // CAMBIO: Se reemplaza el JOIN a 'unidad_medidas' por uno a 'catalogo_datos' con un nuevo alias.
            ->join('catalogo_datos as unidad_medida_catalogo', 'adquisiciones_detalle.unidad_medida_id', '=', 'unidad_medida_catalogo.id')
            ->join('catalogo_datos as tipo_etapa_catalogo', 'adquisiciones.tipo_etapa_id', '=', 'tipo_etapa_catalogo.id')
            ->where('adquisiciones.proyecto_id', $filters['proyecto']);

        // Aplicar filtros
        self::applyCommonFilters($query, $filters, ['subproyecto', 'estado', 'fechas', 'etapa'], 'adquisiciones');
        $query->when($filters['producto'], fn($q) => $q->where('adquisiciones_detalle.articulo_id', $filters['producto']));
        $query->when($filters['necesidad'], fn($q) => $q->where('adquisiciones_detalle.necesidad', $filters['necesidad']));
        $query->when($filters['costo_directo'], fn($q) => $q->where('adquisiciones.etapa_id', $filters['costo_directo']));
        $query->when($filters['tipo_slug'], function ($q, $slug) {
            if (in_array($slug, ['meteriales.herramientas', 'servicios'])) {
                $q->where('tipo_etapa_catalogo.slug', $slug);
            }
        });

        $query->groupBy(
            'etapa_catalogo.descripcion',
            'tipo_etapa_catalogo.slug',
            'adquisiciones_detalle.articulo_id',
            'articulos.descripcion',
            'unidad_medida_catalogo.descripcion' // <-- Columna corregida
        );

        $detalles = $query->get();

        // Reformatear en la estructura final: ['Nombre Etapa' => ['slug' => [...items...]]]
        return $detalles->groupBy('etapa_nombre')->map(function ($itemsPorEtapa) {
            return $itemsPorEtapa->groupBy('tipo_etapa_slug')->map(function ($itemsPorTipo) {
                return $itemsPorTipo->map(function ($item) {
                    return [
                        'articulo_id'    => $item->articulo_id,
                        'articulo'       => $item->articulo_nombre,
                        'unidad_medida'  => $item->unidad_medida_nombre,
                        'cantidad_total' => $item->cantidad_total,
                        'total'          => (float) $item->total_con_iva,
                    ];
                })->values()->all();
            });
        })->all();
    }

    /**
     * Aplica un conjunto de filtros comunes a una consulta Eloquent.
     */
    private static function applyCommonFilters(Builder $query, array $filters, array $applicableFilters, ?string $table = null)
    {
        $prefix = $table ? "{$table}." : '';

        // Filtro por ETAPA (ID del Catálogo de Datos)
        if (in_array('etapa', $applicableFilters)) {
            $query->when($filters['etapa'], fn($q) => $q->where("{$prefix}etapa_id", $filters['etapa']));
        }

        if (in_array('subproyecto', $applicableFilters)) {
            $query->when($filters['subproyecto'], fn($q) => $q->where(DB::raw("TRIM(LOWER({$prefix}subproyecto))"), trim(strtolower($filters['subproyecto']))));
        }
        if (in_array('proveedor', $applicableFilters)) {
            $query->when($filters['proveedor'], fn($q) => $q->where("{$prefix}proveedor_id", $filters['proveedor']));
        }
        if (in_array('estado', $applicableFilters)) {
            $query->when($filters['estado'], function ($q, $estado) use ($prefix) {
                $column = "{$prefix}estado";
                $statusValues = ($estado == 'pendientes') ? ['En Proceso', 'Finalizado'] : ['Completado'];
                $q->whereIn($column, $statusValues);
            });
        }
        if (in_array('fechas', $applicableFilters)) {
            $query->when($filters['fechas'], function ($q) use ($filters, $prefix) {
                list($fechaInicio, $fechaFin) = explode(' - ', $filters['fechas']);
                $q->whereBetween("{$prefix}fecha", [
                    Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->startOfDay(),
                    Carbon::createFromFormat('m/d/Y', trim($fechaFin))->endOfDay(),
                ]);
            });
        }
    }

    /**
     * Ordena el array de resultados final.
     */
    private static function sortFinalResult(array $resultado): array
    {
        // 1. Ordenar los proyectos (claves principales) alfabéticamente
        ksort($resultado, SORT_STRING);

        // 2. Iterar sobre cada proyecto para ordenar sus etapas
        foreach ($resultado as &$etapas) { // Usamos '&' para modificar el array directamente

            // 2.1. Ordenar las etapas (claves secundarias) alfabéticamente
            ksort($etapas, SORT_STRING);

            // 2.2. Ordenar los CONTRATISTAS por proveedor y luego por categoría
            // Se accede directamente a la clave 'contratista' del array de la etapa.
            if (!empty($etapas['contratista'])) {
                usort($etapas['contratista'], function ($a, $b) {
                    // Primero, compara por proveedor
                    $proveedorCmp = strnatcasecmp($a['proveedor'], $b['proveedor']);
                    if ($proveedorCmp !== 0) {
                        return $proveedorCmp;
                    }
                    // Si los proveedores son iguales, compara por categoría
                    return strnatcasecmp($a['categoria'], $b['categoria']);
                });
            }

            // 2.3. Ordenar los MATERIALES por artículo
            if (!empty($etapas['materiales_herramientas'])) {
                usort($etapas['materiales_herramientas'], function ($a, $b) {
                    return strnatcasecmp($a['articulo'], $b['articulo']);
                });
            }

            // 2.4. Ordenar los SERVICIOS por artículo
            if (!empty($etapas['servicios'])) {
                usort($etapas['servicios'], function ($a, $b) {
                    return strnatcasecmp($a['articulo'], $b['articulo']);
                });
            }
        }

        // Buenas prácticas para limpiar la referencia después del bucle
        unset($etapas);

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
