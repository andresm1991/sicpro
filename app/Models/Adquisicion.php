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

        // 2. Obtener las etapas relevantes para el proyecto
        $etapas = $filters['proyectoModel']->etapas()
            ->when($filters['etapa'], fn($q) => $q->where('etapas.id', $filters['etapa']))
            ->get();

        $resultado = [];

        // 3. Procesar cada etapa por separado
        foreach ($etapas as $etapa) {
            $etapaNombre = $etapa->descripcion ?? 'Sin etapa';

            // Inicializa la estructura para esta etapa
            $resultado[$etapaNombre] = [
                'contratista' => [],
                'mano_obra' => [],
                'materiales_herramientas' => [],
                'servicios' => [],
            ];

            // 4. Obtener datos para cada categoría si el filtro de "tipo" lo permite
            if (in_array($filters['tipo_slug'], [null, 'contratista'])) {
                $resultado[$etapaNombre]['contratista'] = self::getContratistasData($filters, $etapa->id);
            }
            if (in_array($filters['tipo_slug'], [null, 'mano.obra'])) {
                $resultado[$etapaNombre]['mano_obra'] = self::getManoDeObraData($filters, $etapa->id);
            }
            if (in_array($filters['tipo_slug'], [null, 'meteriales.herramientas', 'servicios'])) {
                $adquisiciones = self::getAdquisicionesData($filters, $etapa->id);
                $resultado[$etapaNombre]['materiales_herramientas'] = $adquisiciones['meteriales.herramientas'] ?? [];
                $resultado[$etapaNombre]['servicios'] = $adquisiciones['servicios'] ?? [];
            }
        }

        // 5. Ordenar el resultado final
        $resultado = self::sortFinalResult($resultado);

        return [
            'subproyecto' => $filters['subproyecto'],
            'data' => $resultado
        ];
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
     * Obtiene y procesa los datos de Contratistas.
     */
    private static function getContratistasData(array $filters, int $etapaId): array
    {
        $query = \App\Models\Contratista::query()
            ->with(['proveedor:id,razon_social', 'articulo:id,descripcion', 'detalle_contratistas', 'pagosOrdenTrabajoContratista'])
            ->where('proyecto_id', $filters['proyecto'])
            ->where('etapa_id', $etapaId);

        // Aplicar filtros comunes
        self::applyCommonFilters($query, $filters, ['subproyecto', 'proveedor']);

        $contratistas = $query->get();

        // Agrupar en PHP, ya que los cálculos son complejos y se basan en relaciones.
        $contratistasAgrupados = $contratistas->groupBy(function ($item) {
            $proveedorNombre = $item->proveedor->razon_social ?? 'Sin proveedor';
            $categoriaNombre = $item->articulo->descripcion ?? 'Sin categoría';
            return $proveedorNombre . '|' . $categoriaNombre;
        })->map(function ($group) {
            $first = $group->first();
            $totalContratado = $group->sum(function ($contratista) {
                return $contratista->detalle_contratistas->sum(fn($d) => ($d->cantidad ?? 0) * ($d->valor_unitario ?? 0)) * ($contratista->numero_casas ?? 1);
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
        });

        return $contratistasAgrupados->values()->all();
    }

    /**
     * Obtiene y procesa los datos de Mano de Obra.
     */
    private static function getManoDeObraData(array $filters, int $etapaId): array
    {
        $query = \App\Models\ManoObra::query()
            ->where('proyecto_id', $filters['proyecto'])
            ->where('etapa_id', $etapaId);

        // Aplicar filtros comunes
        self::applyCommonFilters($query, $filters, ['subproyecto', 'proveedor']);

        // Obtener la suma total directamente desde la base de datos
        $totalManoObra = $query->clone()
            ->join('detalle_mano_obra', 'mano_obra.id', '=', 'detalle_mano_obra.mano_obra_id')
            ->whereExists(function ($query) { // Solo si tiene pagos
                $query->select(DB::raw(1))
                    ->from('pago_mano_obra')
                    ->whereColumn('pago_mano_obra.mano_obra_id', 'mano_obra.id');
            })
            ->sum(DB::raw('COALESCE(detalle_mano_obra.valor, 0) + COALESCE(detalle_mano_obra.adicional, 0) - COALESCE(detalle_mano_obra.descuento, 0)'));

        $cantidad = $query->distinct('fecha_inicio', 'fecha_fin')->count();

        return [[
            'cantidad' => $cantidad,
            'total' => $totalManoObra
        ]];
    }

    /**
     * Obtiene y procesa los datos de Adquisiciones (Materiales y Servicios).
     */
    private static function getAdquisicionesData(array $filters, int $etapaId): array
    {
        $query = \App\Models\AdquisicionDetalle::query()
            ->select(
                'articulo_id',
                'articulos.descripcion as articulo_nombre',
                'unidad_medidas.descripcion as unidad_medida_nombre',
                'tipo_etapas.slug as tipo_etapa_slug',
                DB::raw('SUM(adquisicion_detalles.cantidad_solicitada) as cantidad_total'),
                DB::raw('SUM( (adquisicion_detalles.cantidad_solicitada * adquisicion_detalles.valor) * (1 + adquisicion_detalles.iva/100) ) as total_con_iva')
            )
            ->join('adquisiciones', 'adquisicion_detalles.adquisicion_id', '=', 'adquisiciones.id')
            ->join('articulos', 'adquisicion_detalles.articulo_id', '=', 'articulos.id')
            ->join('unidad_medidas', 'adquisicion_detalles.unidad_medida_id', '=', 'unidad_medidas.id')
            ->join('tipo_etapas', 'adquisiciones.tipo_etapa_id', '=', 'tipo_etapas.id')
            ->where('adquisiciones.proyecto_id', $filters['proyecto'])
            ->where('adquisiciones.etapa_id', $etapaId);

        // Aplicar filtros comunes a la tabla 'adquisiciones'
        self::applyCommonFilters($query, $filters, ['subproyecto', 'estado', 'fechas'], 'adquisiciones');

        // Filtros específicos de esta consulta
        $query->when($filters['producto'], fn($q) => $q->where('adquisicion_detalles.articulo_id', $filters['producto']));
        $query->when($filters['necesidad'], fn($q) => $q->where('adquisicion_detalles.necesidad', $filters['necesidad']));
        $query->when($filters['costo_directo'], fn($q) => $q->where('adquisiciones.etapa_id', $filters['costo_directo']));

        $query->groupBy('articulo_id', 'articulo_nombre', 'unidad_medida_nombre', 'tipo_etapa_slug');

        // Filtrar por tipo_etapa_slug si es necesario
        $query->when($filters['tipo_slug'], function ($q) use ($filters) {
            if (in_array($filters['tipo_slug'], ['meteriales.herramientas', 'servicios'])) {
                $q->where('tipo_etapas.slug', $filters['tipo_slug']);
            }
        });

        $detalles = $query->get();

        // Agrupar por slug en PHP
        return $detalles->groupBy('tipo_etapa_slug')->map(function ($group) {
            return $group->map(function ($item) {
                return [
                    'articulo_id'    => $item->articulo_id,
                    'articulo'       => $item->articulo_nombre,
                    'unidad_medida'  => $item->unidad_medida_nombre,
                    'cantidad_total' => $item->cantidad_total,
                    'total'          => $item->total_con_iva,
                ];
            });
        })->all();
    }

    /**
     * Aplica un conjunto de filtros comunes a una consulta Eloquent.
     */
    private static function applyCommonFilters(Builder $query, array $filters, array $applicableFilters, string $table = null)
    {
        $prefix = $table ? "{$table}." : '';

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
        ksort($resultado, SORT_STRING); // Ordenar proyectos

        foreach ($resultado as &$etapas) {
            ksort($etapas, SORT_STRING); // Ordenar etapas
            foreach ($etapas as &$categorias) {
                if (!empty($categorias['materiales_herramientas'])) {
                    usort($categorias['materiales_herramientas'], fn($a, $b) => strnatcasecmp($a['articulo'], $b['articulo']));
                }
                if (!empty($categorias['servicios'])) {
                    usort($categorias['servicios'], fn($a, $b) => strnatcasecmp($a['articulo'], $b['articulo']));
                }
                if (!empty($categorias['contratista'])) {
                    usort($categorias['contratista'], function ($a, $b) {
                        $cmp = strnatcasecmp($a['proveedor'], $b['proveedor']);
                        return $cmp !== 0 ? $cmp : strnatcasecmp($a['categoria'], $b['categoria']);
                    });
                }
            }
        }
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
