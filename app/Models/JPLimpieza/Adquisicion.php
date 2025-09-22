<?php

namespace App\Models\JPLimpieza;

use Carbon\Carbon;
use App\Models\Proveedor;
use App\Models\CatalogoDato;
use Illuminate\Support\Facades\DB;
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
        $proyectoInput = $request->input('proyecto');
        $proveedor = $request->input('proveedor');

        if ($request->filled('fechas')) { // Usar filled() es más robusto
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicioF = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFinF = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');
        }

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
            $manoDeObra = ManoObra::with(['proyecto', 'detalles.proveedor'])
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

        $ordenCategorias = [
            'Materiales y Herramientas',
            'Servicios',
            'Contratistas',
            'Mano de Obra',
        ];

        $reporteOrdenado = [];

        // Recorre el arreglo de orden y construye el nuevo arreglo de reporte.
        foreach ($ordenCategorias as $categoria) {
            // Verifica si la categoría existe en los datos que obtuvimos.
            if (isset($reportData[$categoria])) {
                // Si existe, la añade al nuevo arreglo en la posición correcta.
                $reporteOrdenado[$categoria] = $reportData[$categoria];
            }
        }

        // Opcional: Añadir cualquier otra categoría que no esté en la lista de orden al final.
        // Esto hace tu código más robusto si en el futuro se añaden más tipos.
        foreach ($reportData as $categoria => $datos) {
            if (!isset($reporteOrdenado[$categoria])) {
                $reporteOrdenado[$categoria] = $datos;
            }
        }


        $ordenadoPor = $request->input('ordenado', 'fecha');

        // Usamos el operador de referencia (&) para modificar el array directamente.
        foreach ($reporteOrdenado as $categoria => &$datos) {
            switch ($ordenadoPor) {
                case 'alfabetico':
                    usort($datos, function ($a, $b) {
                        // Función para obtener el nombre del proveedor de forma unificada
                        $getNombreProveedor = function ($item) {
                            if (!empty($item['proveedor']['nombre'])) {
                                return $item['proveedor']['nombre']; // Para Adquisiciones y Contratistas
                            }
                            if (!empty($item['detalles'][0]['proveedor']['nombre'])) {
                                return $item['detalles'][0]['proveedor']['nombre']; // Para Mano de Obra
                            }
                            return ''; // Si no hay proveedor
                        };

                        $nombreA = $getNombreProveedor($a);
                        $nombreB = $getNombreProveedor($b);

                        // strnatcasecmp hace una comparación "natural" e insensible a mayúsculas
                        return strnatcasecmp($nombreA, $nombreB);
                    });
                    break;

                case 'secuencial':
                    // "Secuencial" puede significar por ID. Asumiremos ID descendente.
                    // Si no hay ID, mantendrá el orden por defecto (que era por fecha).
                    usort($datos, function ($a, $b) {
                        $idA = $a['id'] ?? 0;
                        $idB = $b['id'] ?? 0;
                        return $idB <=> $idA; // Orden descendente (más nuevo primero)
                    });
                    break;

                case 'fecha':
                default: // 'fecha' será el orden por defecto
                    usort($datos, function ($a, $b) {
                        // La fecha puede estar en 'fecha' o 'fecha_desde'
                        $fechaA_str = $a['fecha'] ?? $a['fecha_desde'];
                        $fechaB_str = $b['fecha'] ?? $b['fecha_desde'];

                        // Creamos objetos Carbon para una comparación segura
                        $fechaA = Carbon::parse($fechaA_str);
                        $fechaB = Carbon::parse($fechaB_str);

                        // <=> es el "spaceship operator". Para orden descendente, invertimos a y b.
                        return $fechaB <=> $fechaA;
                    });
                    break;
            }
        }
        // Deshacer la referencia para evitar efectos secundarios
        unset($datos);


        return $reporteOrdenado;
    }

    public static function dataReportePorArticulo($request)
    {
        // --- PREPARACIÓN DE FILTROS ---
        $fechaInicioF = null;
        $fechaFinF = null;
        $proyectoInput = $request->input('proyecto');
        $proveedorInput = $request->input('proveedor');

        $dbJpLimpieza = DB::connection('mysql_jp_limpieza')->getDatabaseName();
        $dbMysql = DB::connection('mysql')->getDatabaseName();

        if ($request->filled('fechas')) {
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicioF = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFinF = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');
        }

        $reportData = [];

        // --- 1. RESUMEN DE ADQUISICIONES (Agrupado por producto_id) ---
        $queryAdquisiciones = DetalleAdquisicion::query()
            ->select(
                'productos.nombre as articulo',
                "{$dbMysql}.catalogo_datos.descripcion as categoria",
                DB::raw('SUM(detalle_adquisiciones.cantidad) as cantidad_total'),

                // ---- CORRECCIÓN AQUÍ: Calculamos el total con IVA directamente en SQL ----
                DB::raw('SUM( (detalle_adquisiciones.cantidad * detalle_adquisiciones.precio_unitario) * (1 + (detalle_adquisiciones.iva / 100)) ) as monto_total')
            )
            // He corregido el join a productos para que apunte a la base de datos correcta (asumo jp_limpieza)
            ->join("{$dbJpLimpieza}.productos", 'detalle_adquisiciones.producto_id', '=', "{$dbJpLimpieza}.productos.id")
            ->join("{$dbJpLimpieza}.adquisiciones", 'detalle_adquisiciones.adquisicion_id', '=', "{$dbJpLimpieza}.adquisiciones.id")
            ->join("{$dbMysql}.catalogo_datos", "{$dbJpLimpieza}.adquisiciones.tipo_id", '=', "{$dbMysql}.catalogo_datos.id")
            ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                $q->whereBetween('adquisiciones.fecha', [$fechaInicioF, $fechaFinF]);
            })
            ->when($proyectoInput, function ($q) use ($proyectoInput) {
                $q->where('adquisiciones.proyecto_id', is_numeric($proyectoInput) ? $proyectoInput : null);
            })
            ->when($proveedorInput, function ($q) use ($proveedorInput) {
                $q->where('adquisiciones.proveedor_id', $proveedorInput);
            })
            ->when($request->input('tipo'), function ($q) use ($request) {
                $q->where('adquisiciones.tipo_id', $request->input('tipo'));
            })
            ->when($request->input('forma_pago'), function ($q) use ($request) {
                $q->where('forma_pago_id', $request->input('forma_pago'));
            })
            ->whereNotNull('detalle_adquisiciones.producto_id')
            ->groupBy('productos.id', 'productos.nombre', 'categoria');

        $resultadosAdquisiciones = $queryAdquisiciones->get();
        if ($resultadosAdquisiciones->isNotEmpty()) {
            $reportData = $resultadosAdquisiciones->groupBy('categoria')->toArray();
        }

        // --- 2. RESUMEN DE CONTRATISTAS (Agrupado por proveedor_id) ---
        // (Esta sección no necesita cambios, ya que detalle_contratista SÍ tiene una columna 'total')
        $tipoRequest = $request->input('tipo');
        if (!$request->filled('tipo') || ($tipoRequest && CatalogoDato::find($tipoRequest)?->descripcion === 'Contratistas')) {
            $queryContratistas = Contratista::query()
                ->select(
                    DB::raw("COALESCE({$dbMysql}.proveedores.razon_social, CONCAT({$dbMysql}.proveedores.nombres, ' ', {$dbMysql}.proveedores.apellidos)) as articulo"),
                    DB::raw("'Contratistas' as categoria"),
                    DB::raw("COUNT(contratistas.id) as cantidad_total"),
                    DB::raw('SUM(detalle_contratista.total) as monto_total') // Esto es correcto
                )
                ->join("{$dbMysql}.proveedores", "{$dbJpLimpieza}.contratistas.proveedor_id", '=', "{$dbMysql}.proveedores.id")
                ->join("{$dbJpLimpieza}.detalle_contratista", "{$dbJpLimpieza}.contratistas.id", '=', "{$dbJpLimpieza}.detalle_contratista.contratista_id")
                ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                    $q->whereBetween('contratistas.fecha', [$fechaInicioF, $fechaFinF]);
                })
                ->when($proyectoInput, function ($q) use ($proyectoInput) {
                    $q->where('contratistas.proyecto_id', is_numeric($proyectoInput) ? $proyectoInput : null);
                })
                ->when($proveedorInput, function ($q) use ($proveedorInput) {
                    $q->where('contratistas.proveedor_id', $proveedorInput);
                })
                ->groupBy('contratistas.proveedor_id', 'articulo');

            $resultadosContratistas = $queryContratistas->get();
            if ($resultadosContratistas->isNotEmpty()) {
                $reportData['Contratistas'] = $resultadosContratistas->toArray();
            }
        }

        // --- 3. RESUMEN DE MANO DE OBRA (Agrupado por proyecto_id) ---
        // (Esta sección no necesita cambios, ya que detalle_mano_obra SÍ tiene una columna 'total_recibir')
        if (!$request->filled('tipo') || ($tipoRequest && CatalogoDato::find($tipoRequest)?->descripcion === 'Mano de Obra')) {
            $queryManoObra = ManoObra::query()
                ->select(
                    'proyectos.nombre_proyecto as articulo',
                    DB::raw("'Mano de Obra' as categoria"),
                    DB::raw("COUNT(DISTINCT detalle_mano_obra.mano_obra_id) as cantidad_total"),
                    DB::raw('SUM(detalle_mano_obra.total_ingreso + detalle_mano_obra.aporte_patronal) as monto_total') // Esto es correcto
                )
                ->join("{$dbJpLimpieza}.proyectos", "{$dbJpLimpieza}.mano_obra.proyecto_id", '=', "{$dbJpLimpieza}.proyectos.id")
                ->join("{$dbJpLimpieza}.detalle_mano_obra", "{$dbJpLimpieza}.mano_obra.id", '=', "{$dbJpLimpieza}.detalle_mano_obra.mano_obra_id")
                ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                    $q->where(function ($q) use ($fechaInicioF, $fechaFinF) {
                        $q->where('mano_obra.fecha_desde', '<=', $fechaFinF)
                            ->where('mano_obra.fecha_hasta', '>=', $fechaInicioF);
                    });
                })
                ->when($proyectoInput, function ($q) use ($proyectoInput) {
                    $q->where('mano_obra.proyecto_id', is_numeric($proyectoInput) ? $proyectoInput : null);
                })
                ->groupBy('mano_obra.proyecto_id', 'proyectos.nombre_proyecto');

            $resultadosManoObra = $queryManoObra->get();
            if ($resultadosManoObra->isNotEmpty()) {
                $reportData['Mano de Obra'] = $resultadosManoObra->toArray();
            }
        }

        // --- 4. ORDENAR CATEGORÍAS ---
        $ordenCategorias = [
            'Materiales y Herramientas',
            'Servicios',
            'Contratistas',
            'Mano de Obra',
        ];

        $reporteOrdenado = [];
        foreach ($ordenCategorias as $categoria) {
            if (isset($reportData[$categoria])) {
                $reporteOrdenado[$categoria] = $reportData[$categoria];
            }
        }
        foreach ($reportData as $categoria => $datos) {
            if (!isset($reporteOrdenado[$categoria])) {
                $reporteOrdenado[$categoria] = $datos;
            }
        }

        return $reporteOrdenado;
    }
}