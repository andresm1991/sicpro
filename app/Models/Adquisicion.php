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


    /**
     * Reporte de adquisiciones general.
     *
     * @param \Illuminate\Http\Request $request
     * @param bool $gasolina
     * @return \Illuminate\Support\Collection
     */
    public static function dataReporteAdquisiciones($request, bool $gasolina = false): \Illuminate\Support\Collection
    {
        $query = self::with([
            'proyecto',
            'etapa',
            'tipo_etapa',
            'orden_recepcion.forma_pago',
            'orden_recepcion.proveedor'
        ]);

        // Aplicar filtros utilizando un método auxiliar
        self::applyDataReporteFilters($query, $request, $gasolina);
        //dd($query->toSql(), $query->getBindings());

        // Ordenar
        self::applyDataReporteOrdering($query, $request->input('ordenado'), $gasolina);

        // Mapear y calcular totales
        return $query->get()->map(function ($adquisicion) use ($request) {
            $productoId = $request->input('producto');
            $detalles = $productoId
                ? $adquisicion->adquisiciones_detalle->where('articulo_id', $productoId)
                : $adquisicion->adquisiciones_detalle;

            $cantidad = $detalles->sum('cantidad_solicitada');
            $total = $detalles->sum(function ($detalle) {
                return calcularTotalProducto($detalle->cantidad_solicitada, $detalle->valor, $detalle->iva);
            });
            $necesidad = $detalles->pluck('necesidad')->unique()->implode(', ');

            return [
                'adquisicion' => $adquisicion,
                'cantidad' => $cantidad,
                'total' => '$ ' . number_format($total, 4),
                'necesidad' => $necesidad,
            ];
        });
    }

    /**
     * Aplica los filtros a la query de dataReporteAdquisiciones.
     */
    private static function applyDataReporteFilters(Builder $query, $request, bool $gasolina): void
    {
        $query->when($request->filled('proyecto'), fn($q) => $q->where('proyecto_id', $request->input('proyecto')));
        $query->when($request->input('estado'), function ($q, $estado) {
            $q->whereIn('estado', ($estado == 'pendientes') ? ['En Proceso', 'Finalizado'] : ['Completado']);
        });
        $query->when($request->input('etapa'), fn($q) => $q->where('etapa_id', $request->input('etapa')));
        $query->when($request->input('tipo'), fn($q) => $q->where('tipo_etapa_id', $request->input('tipo')));
        $query->when($request->input('costo'), fn($q) => $q->where('etapa_id', $request->input('costo')));
        $query->when($request->input('subproyecto'), fn($q) => $q->where('subproyecto', $request->input('subproyecto')));
        if ($request->input('tipo_reporte') != 'global') {
            $query->when($request->input('tipo_reporte'), fn($q) => $q->where('tipo_adquisicion', $request->input('tipo_reporte')));
        }

        $query->when($request->input('necesidad'), function ($q, $necesidad) {
            $q->whereHas('adquisiciones_detalle', fn($ad) => $ad->where('necesidad', $necesidad))
                ->with(['adquisiciones_detalle' => fn($ad) => $ad->select('adquisicion_id', 'cantidad_solicitada', 'articulo_id')->where('necesidad', $necesidad)]);
        });

        $query->when($request->input('producto'), function ($q, $producto) {
            // Filtramos las adquisiciones que TIENEN un detalle que cumple la siguiente condición.
            $q->whereHas('adquisiciones_detalle', function ($ad) use ($producto) {
                // Verificamos si el input 'producto' es un número (ID) o un string (nombre).
                if (is_numeric($producto)) {
                    // Si es numérico, filtramos directamente por la columna articulo_id en la tabla de detalles.
                    $ad->where('articulo_id', $producto);
                } else {
                    // Si es un string, necesitamos buscar en la tabla de artículos relacionada.
                    // Esto requiere un "whereHas" anidado: filtramos los detalles que TIENEN un producto
                    // cuyo nombre coincide con la búsqueda.
                    // ASUNCIÓN 1: La relación en el modelo AdquisicionDetalle se llama 'producto'.
                    $ad->whereHas('producto', function ($p) use ($producto) {
                        // ASUNCIÓN 2: La columna con el nombre/descripción del artículo se llama 'descripcion'.
                        // Usamos LIKE para permitir búsquedas parciales.
                        $p->where('descripcion', 'LIKE', '%' . $producto . '%');
                    });
                }
            });
        });

        $query->when($request->input('proveedor'), function ($q, $proveedor) {
            $q->whereHas('orden_recepcion', fn($or) => $or->where('proveedor_id', $proveedor));
        });

        $query->when($request->input('fechas'), function ($q, $fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $q->whereBetween('fecha', [
                Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d'),
                Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d')
            ]);
        });

        $query->when($request->input('forma_pago'), function ($q, $formaPago) {
            $q->whereHas('orden_recepcion', fn($or) => $or->where('forma_pago_id', $formaPago));
        });

        if ($gasolina) {
            $query->whereHas('adquisiciones_detalle', function ($q) {
                $q->whereHas('producto', fn($p) => $p->where('descripcion', 'gasolina para camioneta'))
                    ->where('kilometraje', '>', 0);
            });
        }
    }

    /**
     * Aplica el ordenamiento a la query de dataReporteAdquisiciones.
     */
    private static function applyDataReporteOrdering(Builder $query, ?string $ordenado, bool $gasolina): void
    {
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
    }

    /**
     * Reporte comparativo de adquisiciones entre dos proyectos.
     * Esta será nuestra nueva función principal.
     */
    public static function reporteComparativoAdquisiciones($request)
    {
        $proyectoId1 = $request->input('proyecto');
        $proyectoId2 = $request->input('proyecto_2');

        if (!$proyectoId1 || !$proyectoId2) {
            // Opcional: puedes lanzar una excepción o devolver un error.
            return ['error' => 'Se requieren dos proyectos para la comparación.'];
        } elseif ($proyectoId1 == $proyectoId2) {
            // Si ambos proyectos son iguales, devolvemos un error o un mensaje adecuado.
            return ['error' => 'Los proyectos seleccionados son iguales. Por favor, elige dos proyectos diferentes.'];
        }

        // 1. Preparamos los filtros comunes (fechas, estado, etc.), ignorando el proyecto por ahora.
        $commonFilters = self::prepareFilters($request);
        unset($commonFilters['proyecto'], $commonFilters['proyectoModel'], $commonFilters['proyectoNombre']);

        // 2. Generamos los datos para cada proyecto por separado
        $datosProyecto1 = self::generarDatosParaUnProyecto($proyectoId1, $commonFilters);
        $datosProyecto2 = self::generarDatosParaUnProyecto($proyectoId2, $commonFilters);

        // 3. Combinamos los resultados en una única estructura comparativa
        $datosCombinados = self::combinarDatosProyectos($datosProyecto1['data'], $datosProyecto2['data']);

        // 4. Devolvemos todo en un formato listo para la vista
        return [
            'proyecto1' => [
                'id' => $proyectoId1,
                'nombre' => $datosProyecto1['proyecto'],
            ],
            'proyecto2' => [
                'id' => $proyectoId2,
                'nombre' => $datosProyecto2['proyecto'],
            ],
            'subproyecto' => $datosProyecto1['subproyecto'], // Asumimos que el subproyecto es el mismo filtro para ambos
            'data_comparativa' => $datosCombinados,
        ];
    }

    /**
     * Reporte global
     */

    public static function reporteGlobalAdquisiciones($request)
    {
        // Obtenemos los filtros comunes del request
        $filters = self::prepareFilters($request);

        // Si no hay proyecto, no hacemos nada
        if (!$filters['proyectoModel']) {
            return ['data' => [], 'subproyecto' => null, 'proyecto' => null];
        }

        // Llamamos a nuestra nueva función refactorizada
        return self::generarDatosParaUnProyecto($filters['proyecto'], $filters);
    }

    /**
     * NUEVA FUNCIÓN REFACTORIZADA
     * Genera todos los datos del reporte para UN SOLO proyecto.
     *
     * @param int $proyectoId El ID del proyecto a procesar.
     * @param array $filters Un array con todos los filtros aplicados (fecha, estado, etc.).
     * @return array Los datos estructurados para el proyecto.
     */
    private static function generarDatosParaUnProyecto(int $proyectoId, array $filters): array
    {
        $proyectoModel = Proyecto::find($proyectoId);
        if (!$proyectoModel) {
            return ['data' => [], 'subproyecto' => $filters['subproyecto'], 'proyecto' => 'Proyecto no encontrado'];
        }

        // Preparamos los filtros específicos para esta ejecución
        $filters['proyecto'] = $proyectoId; // ¡Importante! Aseguramos el ID del proyecto.
        $filters['proyectoModel'] = $proyectoModel;
        $filters['proyectoNombre'] = $proyectoModel->nombre_proyecto;

        $resultado = [];

        // Creamos una variable para saber si el filtro de producto está activo.
        $productoFilterActive = !empty($filters['producto']);

        // 1. Obtener datos de Contratistas y Mano de Obra SÓLO si NO estamos filtrando por producto.
        if (!$productoFilterActive) {
            if (in_array($filters['tipo_slug'], [null, 'contratista'])) {
                $contratistas = self::getContratistasData($filters);
                self::mergeResults($resultado, $contratistas, 'contratista');
            }
            if (in_array($filters['tipo_slug'], [null, 'mano.obra'])) {
                $manosDeObra = self::getManoDeObraData($filters);
                self::mergeResults($resultado, $manosDeObra, 'mano_obra');
            }
        }

        // 2. Obtener datos de Adquisiciones. Esto siempre se intenta, ya que es la única
        // sección que puede responder al filtro de producto. La lógica interna de la
        // función getAdquisicionesData ya se encarga de aplicar el filtro si existe.
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
                'unidad_medida_catalogo.descripcion as unidad_medida_nombre',
                DB::raw('SUM(adquisiciones_detalle.cantidad_solicitada) as cantidad_total'),
                DB::raw('SUM( (adquisiciones_detalle.cantidad_solicitada * adquisiciones_detalle.valor) * (1 + adquisiciones_detalle.iva/100) ) as total_con_iva')
            )
            ->join('adquisiciones', 'adquisiciones_detalle.adquisicion_id', '=', 'adquisiciones.id')
            ->join('orden_recepciones', 'adquisiciones.id', '=', 'orden_recepciones.adquisicion_id')
            ->join('catalogo_datos as etapa_catalogo', 'adquisiciones.etapa_id', '=', 'etapa_catalogo.id')
            ->join('articulos', 'adquisiciones_detalle.articulo_id', '=', 'articulos.id')
            ->join('catalogo_datos as unidad_medida_catalogo', 'adquisiciones_detalle.unidad_medida_id', '=', 'unidad_medida_catalogo.id')
            ->join('catalogo_datos as tipo_etapa_catalogo', 'adquisiciones.tipo_etapa_id', '=', 'tipo_etapa_catalogo.id')
            ->where('adquisiciones.proyecto_id', $filters['proyecto']);

        $columnOverrides = ['proveedor' => 'orden_recepciones.proveedor_id'];
        // Aplicar filtros
        self::applyCommonFilters($query, $filters, ['subproyecto', 'proveedor', 'estado', 'fechas', 'etapa'], 'adquisiciones', $columnOverrides);

        $query->when($filters['producto'], function ($q, $productoValue) {
            if (is_numeric($productoValue)) {
                // Si el valor es numérico, se asume que es un ID y se busca por 'articulo_id'
                $q->where('adquisiciones_detalle.articulo_id', $productoValue);
            } else {
                // Si el valor es un string, se realiza una búsqueda 'LIKE' en la descripción del artículo
                $q->where('articulos.descripcion', 'like', '%' . $productoValue . '%');
            }
        });

        $query->when($filters['necesidad'], fn($q) => $q->where('adquisiciones_detalle.necesidad', $filters['necesidad']));
        $query->when($filters['costo_directo'], fn($q) => $q->where('adquisiciones.etapa_id', $filters['costo_directo']));
        $query->when($filters['tipo_slug'], function ($q, $slug) {
            if (in_array($slug, ['meteriales.herramientas', 'servicios'])) {
                $q->where('tipo_etapa_catalogo.slug', $slug);
            }
        });

        // --- INICIO DE LA CORRECCIÓN ---
        // Se añade 'adquisiciones_detalle.unidad_medida_id' al GROUP BY.
        // Esto asegura que si un artículo tiene dos unidades de medida diferentes,
        // se traten como dos filas distintas en el resultado.
        $query->groupBy(
            'etapa_catalogo.descripcion',
            'tipo_etapa_catalogo.slug',
            'adquisiciones_detalle.articulo_id',
            'articulos.descripcion',
            'adquisiciones_detalle.unidad_medida_id', // <-- ¡LA LÍNEA CLAVE!
            'unidad_medida_catalogo.descripcion'
        );
        // --- FIN DE LA CORRECCIÓN ---

        $detalles = $query->get();

        // El resto de la función para formatear los resultados no necesita cambios.
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
    private static function applyCommonFilters(Builder $query, array $filters, array $applicableFilters, ?string $table = null, array $columnOverrides = [])
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
            $query->when($filters['proveedor'], function ($q) use ($filters, $prefix, $columnOverrides) {
                // Si se especificó una columna para 'proveedor', úsala. Si no, usa la predeterminada.
                $column = $columnOverrides['proveedor'] ?? "{$prefix}proveedor_id";
                $q->where($column, $filters['proveedor']);
            });
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

    /**
     * Combina los datos de dos proyectos en una estructura comparativa.
     */
    private static function combinarDatosProyectos(array $dataProyecto1, array $dataProyecto2): array
    {
        // Obtenemos todas las claves de etapa de ambos proyectos para no omitir ninguna
        $etapasKeys = array_unique(array_merge(array_keys($dataProyecto1), array_keys($dataProyecto2)));
        sort($etapasKeys);

        $resultadoFinal = [];

        foreach ($etapasKeys as $etapaNombre) {
            $etapa1 = $dataProyecto1[$etapaNombre] ?? [];
            $etapa2 = $dataProyecto2[$etapaNombre] ?? [];

            // Combinamos cada categoría
            $resultadoFinal[$etapaNombre] = [
                'contratista' => self::combinarCategoria(
                    $etapa1['contratista'] ?? [],
                    $etapa2['contratista'] ?? [],
                    // Función para generar una clave única para cada contratista
                    fn($item) => $item['proveedor'] . '|' . $item['categoria']
                ),
                'mano_obra' => self::combinarCategoria(
                    $etapa1['mano_obra'] ?? [],
                    $etapa2['mano_obra'] ?? [],
                    // Mano de obra solo tiene un registro, la clave es simple
                    fn($item) => 'mano_de_obra_total'
                ),
                'materiales_herramientas' => self::combinarCategoria(
                    $etapa1['materiales_herramientas'] ?? [],
                    $etapa2['materiales_herramientas'] ?? [],
                    // La clave única para un material es su ID de artículo
                    fn($item) => $item['articulo_id']
                ),
                'servicios' => self::combinarCategoria(
                    $etapa1['servicios'] ?? [],
                    $etapa2['servicios'] ?? [],
                    // La clave única para un servicio es su ID de artículo
                    fn($item) => $item['articulo_id']
                ),
            ];
        }

        return $resultadoFinal;
    }

    /**
     * Función auxiliar para combinar los items de una categoría específica.
     */
    private static function combinarCategoria(array $items1, array $items2, callable $keyGenerator): array
    {
        $mapaCombinado = [];

        // Procesamos el primer proyecto
        foreach ($items1 as $item) {
            $key = $keyGenerator($item);
            $mapaCombinado[$key]['item_base'] = $item; // Guardamos los datos base (nombre, etc.)
            $mapaCombinado[$key]['p1'] = $item;
        }

        // Procesamos el segundo proyecto
        foreach ($items2 as $item) {
            $key = $keyGenerator($item);
            if (!isset($mapaCombinado[$key])) {
                $mapaCombinado[$key]['item_base'] = $item; // El item solo existe en el proyecto 2
            }
            $mapaCombinado[$key]['p2'] = $item;
        }

        // Calculamos las diferencias
        foreach ($mapaCombinado as $key => &$value) {
            $p1 = $value['p1'] ?? null;
            $p2 = $value['p2'] ?? null;

            // Ejemplo de cálculo de diferencias (ajusta según tus necesidades)
            $value['diff']['cantidad'] = ($p1['cantidad'] ?? $p1['cantidad_total'] ?? 0) - ($p2['cantidad'] ?? $p2['cantidad_total'] ?? 0);
            $value['diff']['total'] = ($p1['total'] ?? $p1['total_contratado'] ?? 0) - ($p2['total'] ?? $p2['total_contratado'] ?? 0);
            $value['diff']['pagos'] = ($p1['pagos'] ?? 0) - ($p2['pagos'] ?? 0);
            $value['diff']['saldo'] = ($p1['saldo'] ?? 0) - ($p2['saldo'] ?? 0);
        }

        return array_values($mapaCombinado); // Devolvemos como un array indexado
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