<?php

namespace App\Models;

use App\Models\Marketing\ProcesoVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proyecto extends Model
{
    use HasFactory;
    protected $table = 'proyectos';
    protected $fillable = [
        'catalogo_proyecto_id',
        'nombre_proyecto',
        'nombre_propietario',
        'ubicacion',
        'direccion',
        'telefono',
        'correo',
        'tipo_proyecto_id',
        'area_lote',
        'area_construccion',
        'numero_unidades',
        'area_lote_unidad',
        'area_construccion_unidad',
        'presupuesto_total',
        'presupuesto_unidad',
        'fecha_inicio',
        'fecha_finalizacion',
        'observacion',
        'portada',
        'costo_indirecto',
        'estado_id',
    ];

    public function tipo_proyecto()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_proyecto_id');
    }

    public function catalogo_proyecto()
    {
        return $this->belongsTo(CatalogoDato::class, 'catalogo_proyecto_id');
    }

    public function archivos_proyecto()
    {
        return $this->hasMany(ArchivoProyecto::class);
    }

    public function adquisiciones()
    {
        return $this->hasMany(Adquisicion::class);
    }

    public function presupuesto()
    {
        return $this->hasOne(PresupuestoProyecto::class);
    }

    public function cronograma_dias_semana()
    {
        return $this->hasMany(Cronograma::class);
    }

    public function mano_obra()
    {
        return $this->hasMany(ManoObra::class);
    }

    public function contratista()
    {
        return $this->hasMany(Contratista::class);
    }
    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function procesoVentas()
    {
        return $this->hasMany(ProcesoVenta::class, 'proyecto_id');
    }

    public static function presupuestoValorado($proyectoId)
    {
        return CategoriaPresupuesto::whereHas('rubrosPresupuesto.presupuestoProyectos', function ($query) use ($proyectoId) {
            $query->where('proyecto_id', $proyectoId);
        })
            ->with([
                'rubrosPresupuesto' => function ($query) use ($proyectoId) {
                    $query->whereHas('presupuestoProyectos', function ($q) use ($proyectoId) {
                        $q->where('proyecto_id', $proyectoId);
                    })->with([
                                'presupuestoProyectos' => function ($q) use ($proyectoId) {
                                    $q->where('proyecto_id', $proyectoId);
                                }
                            ]);
                }
            ])
            ->get();
    }

    public function getSemanasTranscurridasAttribute()
    {
        $primerRegistro = $this->mano_obra('created_at', 'asc')
            ->first();

        if (!$primerRegistro) {
            return 1; // O el valor que consideres adecuado si no hay mano de obra
        }
        return semanaEnCurso($primerRegistro->fecha_inicio);

        //return ceil((Carbon::parse($primerRegistro->fecha_inicio)->diffInDays(now()) + 1) / 7);
        //return semanasTranscurridas($primerRegistro->fecha_inicio);
    }

    public function getSubproyectosUnicosAttribute()
    {
        // Obtener subproyectos únicos de cada relación
        $subproyectosProyecto = collect([$this->subproyecto])->filter()->unique();

        $subproyectosAdquisiciones = $this->adquisiciones->pluck('subproyecto')->filter()->unique();
        $subproyectosManoObra = $this->mano_obra->pluck('subproyecto')->filter()->unique();
        $subproyectosContratista = $this->contratista->pluck('subproyecto')->filter()->unique();

        // Unir y agrupar todos los subproyectos, eliminando duplicados
        $subproyectos = $subproyectosProyecto
            ->merge($subproyectosAdquisiciones)
            ->merge($subproyectosManoObra)
            ->merge($subproyectosContratista)
            ->unique()
            ->values();

        // Array asociativo para un select
        return $subproyectos->mapWithKeys(function ($item) {
            return [$item => $item];
        })->prepend('', '');
    }


    public static function dataReporteBalance($request)
    {
        // --- 1. PREPARACIÓN DE FILTROS ---
        $tipoReporte = $request->input('tipo_reporte');
        $proyectoInput = $request->input('proyecto');
        $subproyectoInput = $request->input('subproyecto');
        $etapa = $request->input('etapa');
        $tipoEtapa = $request->input('tipo_etapa');

        $fechaInicioF = null;
        $fechaFinF = null;
        $anioF = null;

        if ($tipoReporte === 'balance_proyecto' && $request->filled('fechas')) {
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicioF = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFinF = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');
        } elseif ($tipoReporte === 'balance_global' && $request->filled('anio')) {
            $anioF = $request->input('anio');
        }

        // --- 2. PRE-CÁLCULO DE INGRESOS Y GASTOS POR PROYECTO ---

        // Ingreso A: Ventas (ProcesoVenta) - ESTÁ BIEN
        $ingresosVentasPorProyecto = DB::table('proceso_ventas')
            ->select('proyecto_id', DB::raw('SUM(valor_reserva + valor_saldo_reserva + monto_desembolsado) as total'))
            ->when($tipoReporte === 'balance_global' && $anioF, fn($q) => $q->whereYear('created_at', $anioF))
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, fn($q) => $q->whereBetween('created_at', [$fechaInicioF, $fechaFinF]))
            ->whereNotNull('proyecto_id')
            ->groupBy('proyecto_id')
            ->pluck('total', 'proyecto_id');

        // Ingreso B: Proformas de Adecentamiento - CORREGIDO CON GROUP BY
        $proformasViaAdquisiciones = DB::table('proforma_adecentamientos as pa')
            ->join('catalogo_datos as cd', 'pa.estado_id', '=', 'cd.id')
            ->join('adquisiciones as a', 'pa.numero', '=', 'a.nro_proforma')
            ->select('a.proyecto_id', 'pa.total', 'pa.fecha')
            ->when($tipoReporte === 'balance_proyecto' && $subproyectoInput, fn($q) => $q->where('a.subproyecto', $subproyectoInput))
            ->when($etapa, fn($q) => $q->where('a.etapa_id', $etapa))
            ->when($tipoEtapa, fn($q) => $q->where('a.tipo_etapa_id', $tipoEtapa))
            ->when($tipoReporte === 'balance_global' && $anioF, fn($q) => $q->whereYear('pa.fecha', $anioF))
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, fn($q) => $q->whereBetween('pa.fecha', [$fechaInicioF, $fechaFinF]))
            ->where('cd.slug', 'estados.proformas.facturado')
            ->whereNotNull('a.proyecto_id')
            ->groupBy('pa.numero', 'a.proyecto_id', 'pa.total', 'pa.fecha');

        $proformasViaContratistas = DB::table('proforma_adecentamientos as pa')
            ->join('catalogo_datos as cd', 'pa.estado_id', '=', 'cd.id')
            ->join('contratistas as c', 'pa.numero', '=', 'c.nro_proforma')
            ->select('c.proyecto_id', 'pa.total', 'pa.fecha')
            ->when($tipoReporte === 'balance_proyecto' && $subproyectoInput, fn($q) => $q->where('c.subproyecto', $subproyectoInput))
            ->when($etapa, fn($q) => $q->where('c.etapa_id', $etapa))
            ->when($tipoEtapa, fn($q) => $q->where('c.tipo_etapa_id', $tipoEtapa))
            ->when($tipoReporte === 'balance_global' && $anioF, fn($q) => $q->whereYear('pa.fecha', $anioF))
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, fn($q) => $q->whereBetween('pa.fecha', [$fechaInicioF, $fechaFinF]))
            ->where('cd.slug', 'estados.proformas.facturado')
            ->whereNotNull('c.proyecto_id')
            ->groupBy('pa.numero', 'c.proyecto_id', 'pa.total', 'pa.fecha');

        $proformasViaManoObra = DB::table('proforma_adecentamientos as pa')
            ->join('catalogo_datos as cd', 'pa.estado_id', '=', 'cd.id')
            ->join('mano_obra as m', 'pa.numero', '=', 'm.nro_proforma')
            ->select('m.proyecto_id', 'pa.total', 'pa.fecha')
            ->when($tipoReporte === 'balance_proyecto' && $subproyectoInput, fn($q) => $q->where('m.subproyecto', $subproyectoInput))
            ->when($etapa, fn($q) => $q->where('m.etapa_id', $etapa))
            ->when($tipoEtapa, fn($q) => $q->where('m.tipo_etapa_id', $tipoEtapa))
            ->when($tipoReporte === 'balance_global' && $anioF, fn($q) => $q->whereYear('pa.fecha', $anioF))
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, fn($q) => $q->whereBetween('pa.fecha', [$fechaInicioF, $fechaFinF]))
            ->where('cd.slug', 'estados.proformas.facturado')
            ->whereNotNull('m.proyecto_id')
            ->groupBy('pa.numero', 'm.proyecto_id', 'pa.total', 'pa.fecha');

        $unionDeProformas = $proformasViaAdquisiciones->unionAll($proformasViaContratistas)->unionAll($proformasViaManoObra);

        $ingresosProformasPorProyecto = DB::query()
            ->fromSub($unionDeProformas, 'ingresos_proforma')
            ->select('proyecto_id', DB::raw('SUM(total) as total_ingreso'))
            ->groupBy('proyecto_id')
            ->pluck('total_ingreso', 'proyecto_id');

        // Gasto A: Adquisiciones - REVISAR POSIBLES DUPLICADOS
        $resultadosAdquisiciones = DB::table('adquisiciones_detalle')
            ->join('adquisiciones', 'adquisiciones_detalle.adquisicion_id', '=', 'adquisiciones.id')
            ->select(
                'adquisiciones.proyecto_id',
                'adquisiciones.tipo_adquisicion',
                DB::raw('SUM((adquisiciones_detalle.cantidad_solicitada * adquisiciones_detalle.valor) * (1 + (adquisiciones_detalle.iva / 100))) as total')
            )
            ->when($tipoReporte === 'balance_global' && $anioF, fn($q) => $q->whereYear('adquisiciones.fecha', $anioF))
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, fn($q) => $q->whereBetween('adquisiciones.fecha', [$fechaInicioF, $fechaFinF]))
            ->when($tipoReporte === 'balance_proyecto' && $subproyectoInput, fn($q) => $q->where('adquisiciones.subproyecto', $subproyectoInput))
            ->when($etapa, fn($q) => $q->where('adquisiciones.etapa_id', $etapa))
            ->when($tipoEtapa, fn($q) => $q->where('adquisiciones.tipo_etapa_id', $tipoEtapa))
            ->where('adquisiciones.estado', 'Completado')
            ->whereNotNull('adquisiciones.proyecto_id')
            ->groupBy('adquisiciones.proyecto_id', 'adquisiciones.tipo_adquisicion')
            ->get();

        $gastosAdquisicionesArray = [];
        foreach ($resultadosAdquisiciones as $item) {
            $proyectoId = $item->proyecto_id;
            $tipo = in_array($item->tipo_adquisicion, ['operativo', 'administrativo']) ? $item->tipo_adquisicion : 'operativo';

            if (!isset($gastosAdquisicionesArray[$proyectoId])) {
                $gastosAdquisicionesArray[$proyectoId] = ['operativo' => 0, 'administrativo' => 0];
            }

            $gastosAdquisicionesArray[$proyectoId][$tipo] += $item->total;
        }
        $gastosAdquisicionesSeparados = collect($gastosAdquisicionesArray);

        // Gasto B: Contratistas - REVISAR POSIBLES DUPLICADOS
        $gastosContratistas = DB::table('pagos_orden_trabajo_contratista as potc')
            ->join('contratistas', 'potc.contratista_id', '=', 'contratistas.id')
            ->select('contratistas.proyecto_id', DB::raw('SUM(potc.valor) as total'))
            ->when($tipoReporte === 'balance_global' && $anioF, fn($q) => $q->whereYear('potc.fecha', $anioF))
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, fn($q) => $q->whereBetween('potc.fecha', [$fechaInicioF, $fechaFinF]))
            ->when($tipoReporte === 'balance_proyecto' && $subproyectoInput, fn($q) => $q->where('contratistas.subproyecto', $subproyectoInput))
            ->when($etapa, fn($q) => $q->where('contratistas.etapa_id', $etapa))
            ->when($tipoEtapa, fn($q) => $q->where('contratistas.tipo_etapa_id', $tipoEtapa))
            ->where('potc.pagado', true)
            ->whereNotNull('contratistas.proyecto_id')
            ->groupBy('contratistas.proyecto_id')
            ->pluck('total', 'proyecto_id');

        // Gasto C: Mano de Obra - REVISAR FILTROS Y POSIBLES DUPLICADOS
        $gastosManoObra = DB::table('mano_obra')
            ->join('detalle_mano_obra', 'mano_obra.id', '=', 'detalle_mano_obra.mano_obra_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pagos_mano_obra')
                    ->whereColumn('pagos_mano_obra.mano_obra_id', 'mano_obra.id');
            })
            ->select(
                'mano_obra.proyecto_id',
                DB::raw('SUM(COALESCE(detalle_mano_obra.valor, 0) + COALESCE(detalle_mano_obra.adicional, 0) - COALESCE(detalle_mano_obra.descuento, 0)) as total')
            )
            ->when($tipoReporte === 'balance_global' && $anioF, function ($q) use ($anioF) {
                // IMPORTANTE: Verificar si esta lógica es correcta
                // Quizás debería ser fecha de pago, no fechas de inicio/fin
                $q->where(function ($query) use ($anioF) {
                    $query->whereYear('mano_obra.fecha_inicio', '<=', $anioF)
                        ->whereYear('mano_obra.fecha_fin', '>=', $anioF);
                });
            })
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, function ($q) use ($fechaInicioF, $fechaFinF) {
                // REVISAR: ¿Debería filtrar por fecha_pago o por fecha_inicio/fin?
                $q->where(function ($query) use ($fechaInicioF, $fechaFinF) {
                    $query->where('mano_obra.fecha_inicio', '<=', $fechaFinF)
                        ->where('mano_obra.fecha_fin', '>=', $fechaInicioF);
                    // Alternativa: filtrar por fecha de pago si existe
                    // if (Schema::hasColumn('mano_obra', 'fecha_pago')) {
                    //     $query->orWhereBetween('mano_obra.fecha_pago', [$fechaInicioF, $fechaFinF]);
                    // }
                });
            })
            ->when($tipoReporte === 'balance_proyecto' && $subproyectoInput, fn($q) => $q->where('mano_obra.subproyecto', $subproyectoInput))
            ->when($etapa, fn($q) => $q->where('mano_obra.etapa_id', $etapa))
            ->when($tipoEtapa, fn($q) => $q->where('mano_obra.tipo_etapa_id', $tipoEtapa))
            ->whereNotNull('mano_obra.proyecto_id')
            ->groupBy('mano_obra.proyecto_id')
            ->pluck('total', 'proyecto_id');



        // --- 3. PRE-CÁLCULO DE GASTOS ADMINISTRATIVOS (SIN PROYECTO) ---
        $gastoAdminTotalOperativo = 0;
        $gastoAdminTotalAdministrativo = 0;

        if ($tipoReporte === 'balance_global') {
            // Gasto Admin A: Adquisiciones
            $gastoAdminAdquisiciones = DB::table('adquisiciones_detalle')
                ->join('adquisiciones', 'adquisiciones_detalle.adquisicion_id', '=', 'adquisiciones.id')
                ->select('adquisiciones.tipo_adquisicion', DB::raw('SUM((adquisiciones_detalle.cantidad_solicitada * adquisiciones_detalle.valor) * (1 + (adquisiciones_detalle.iva / 100))) as total'))
                ->when($anioF, fn($q) => $q->whereYear('adquisiciones.fecha', $anioF))
                ->where('adquisiciones.estado', 'Completado')
                ->where('adquisiciones.proyecto_id', 0)
                ->groupBy('adquisiciones.tipo_adquisicion')
                ->pluck('total', 'tipo_adquisicion');

            // Gasto Admin B: Contratistas
            $gastoAdminContratistas = DB::table('pagos_orden_trabajo_contratista as potc')
                ->join('contratistas', 'potc.contratista_id', '=', 'contratistas.id')
                ->when($anioF, fn($q) => $q->whereYear('potc.fecha', $anioF))
                ->where('potc.pagado', true)
                ->where('contratistas.proyecto_id', 0)
                ->sum('potc.valor');

            $gastoAdminTotalOperativo = $gastoAdminAdquisiciones->get('operativo', 0) + $gastoAdminContratistas;
            $gastoAdminTotalAdministrativo = $gastoAdminAdquisiciones->get('administrativo', 0);
        }

        $gastoAdministrativoTotal = $gastoAdminTotalOperativo + $gastoAdminTotalAdministrativo;

        // --- 4. OBTENER PROYECTOS Y CONSOLIDAR DATOS ---
        $estadosEjecutadosIds = CatalogoDato::whereIn('slug', ['estados.proyectos.ejecucion', 'estados.proyectos.finalizado'])->pluck('id');

        $proyectosQuery = self::query()
            ->when($proyectoInput, function ($q) use ($proyectoInput) {
                $q->where('id', $proyectoInput);
            })
            ->when($tipoReporte === 'balance_global' && $anioF, fn($q) => $q->whereYear('created_at', $anioF))
            ->when($tipoReporte === 'balance_proyecto' && $fechaInicioF, fn($q) => $q->whereBetween('created_at', [$fechaInicioF, $fechaFinF]));

        if ($estadosEjecutadosIds->isNotEmpty()) {
            $proyectosQuery->whereIn('estado_id', $estadosEjecutadosIds);
        }

        if ($tipoReporte === 'balance_proyecto' && $proyectoInput) {
            $gastoAdministrativoTotal = 0;
        }

        $proyectos = $proyectosQuery->orderBy('nombre_proyecto', 'asc')->get();

        $balance = $proyectos->map(function ($proyecto) use ($ingresosVentasPorProyecto, $ingresosProformasPorProyecto, $gastosAdquisicionesSeparados, $gastosContratistas, $gastosManoObra) {
            // Ingresos
            $ingresosVentas = $ingresosVentasPorProyecto->get($proyecto->id, 0);
            $ingresosProformas = $ingresosProformasPorProyecto->get($proyecto->id, 0);
            $totalIngresos = $ingresosVentas + $ingresosProformas;

            // Gastos (SEPARADOS)
            $gastosAdq = $gastosAdquisicionesSeparados->get($proyecto->id, ['operativo' => 0, 'administrativo' => 0]);
            $gastoB = $gastosContratistas->get($proyecto->id, 0);
            $gastoC = $gastosManoObra->get($proyecto->id, 0);

            $gastosOperativos = $gastosAdq['operativo'] + $gastoB + $gastoC;
            $gastosAdministrativos = $gastosAdq['administrativo'];

            $totalGastos = $gastosOperativos + $gastosAdministrativos;
            $utilidad = $totalIngresos - $totalGastos;

            return [
                'proyecto_id' => $proyecto->id,
                'proyecto_nombre' => $proyecto->nombre_proyecto,
                'proyecto_tipo_id' => $proyecto->catalogo_proyecto_id,
                'proyecto_tipo' => optional(CatalogoDato::find($proyecto->catalogo_proyecto_id))->descripcion,
                'total_ingresos' => $totalIngresos,
                'gastos_operativos' => $gastosOperativos,
                'gastos_administrativos' => $gastosAdministrativos,
                'total_gastos' => $totalGastos,
                'utilidad' => $utilidad
            ];
        });

        // --- 5. AÑADIR FILA DE GASTOS ADMINISTRATIVOS (CONDICIONAL) ---
        if ($tipoReporte === 'balance_global' && $gastoAdministrativoTotal > 0) {
            $balance->push([
                'proyecto_id' => 'admin',
                'proyecto_nombre' => 'General (Gastos Administrativos)',
                'proyecto_tipo_id' => null,
                'proyecto_tipo' => null,
                'total_ingresos' => 0,
                'gastos_operativos' => $gastoAdminTotalOperativo,
                'gastos_administrativos' => $gastoAdminTotalAdministrativo,
                'total_gastos' => $gastoAdministrativoTotal,
                'utilidad' => -$gastoAdministrativoTotal
            ]);
        }

        return $balance;
    }
}