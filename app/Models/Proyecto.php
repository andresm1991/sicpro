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
            ->with(['rubrosPresupuesto' => function ($query) use ($proyectoId) {
                $query->whereHas('presupuestoProyectos', function ($q) use ($proyectoId) {
                    $q->where('proyecto_id', $proyectoId);
                })->with(['presupuestoProyectos' => function ($q) use ($proyectoId) {
                    $q->where('proyecto_id', $proyectoId);
                }]);
            }])
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
        $fechaInicioF = null;
        $fechaFinF = null;
        $proyectoInput = $request->input('proyecto');

        if ($request->filled('fechas')) {
            list($inicio, $fin) = explode(' - ', $request->input('fechas'));
            $fechaInicioF = Carbon::createFromFormat('m/d/Y', trim($inicio))->format('Y-m-d');
            $fechaFinF = Carbon::createFromFormat('m/d/Y', trim($fin))->format('Y-m-d');
        }

        // --- 2. PRE-CÁLCULO DE GASTOS POR PROYECTO ---

        // Ingreso A: Ventas (ProcesoVenta) - Renombrado para mayor claridad
        $ingresosVentasPorProyecto = DB::table('proceso_ventas')
            ->select('proyecto_id', DB::raw('SUM(valor_reserva + valor_saldo_reserva + monto_desembolsado) as total'))
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('created_at', [$fechaInicioF, $fechaFinF]))
            ->whereNotNull('proyecto_id')
            ->groupBy('proyecto_id')
            ->pluck('total', 'proyecto_id');

        // Ingreso B: Proformas de Adecentamiento (NUEVO)
        // Parte 1: Proformas vinculadas a través de Adquisiciones
        $proformasViaAdquisiciones = DB::table('proforma_adecentamientos as pa')
            ->join('catalogo_datos as cd', 'pa.estado_id', '=', 'cd.id')
            ->join('adquisiciones as a', 'pa.numero', '=', 'a.nro_proforma')
            ->select('a.proyecto_id', 'pa.total', 'pa.fecha')
            ->where('cd.slug', 'estados.proformas.facturado')
            ->whereNotNull('a.proyecto_id');

        // Parte 2: Proformas vinculadas a través de Contratistas
        // Usamos UNION ALL para combinar con la consulta anterior.
        $proformasViaContratistas = DB::table('proforma_adecentamientos as pa')
            ->join('catalogo_datos as cd', 'pa.estado_id', '=', 'cd.id')
            ->join('contratistas as c', 'pa.numero', '=', 'c.nro_proforma')
            ->select('c.proyecto_id', 'pa.total', 'pa.fecha')
            ->where('cd.slug', 'estados.proformas.facturado')
            ->whereNotNull('c.proyecto_id');

        // Agregación Final: Sumamos los totales de ambas fuentes
        $ingresosProformasPorProyecto = DB::query()
            ->fromSub($proformasViaAdquisiciones->unionAll($proformasViaContratistas), 'ingresos_proforma')
            ->select('proyecto_id', DB::raw('SUM(total) as total_ingreso'))
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('fecha', [$fechaInicioF, $fechaFinF]))
            ->groupBy('proyecto_id')
            ->pluck('total_ingreso', 'proyecto_id');

        // Gasto A: Adquisiciones
        $gastosAdquisiciones = DB::table('adquisiciones_detalle')
            ->join('adquisiciones', 'adquisiciones_detalle.adquisicion_id', '=', 'adquisiciones.id')
            ->select('adquisiciones.proyecto_id', DB::raw('SUM((adquisiciones_detalle.cantidad_solicitada * adquisiciones_detalle.valor) * (1 + (adquisiciones_detalle.iva / 100))) as total'))
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('adquisiciones.fecha', [$fechaInicioF, $fechaFinF]))
            ->whereNotNull('adquisiciones.proyecto_id') // Solo gastos asignados a proyectos
            ->groupBy('adquisiciones.proyecto_id')
            ->pluck('total', 'proyecto_id');

        // Gasto B: Contratistas
        $gastosContratistas = DB::table('pagos_orden_trabajo_contratista as potc')
            ->join('contratistas', 'potc.contratista_id', '=', 'contratistas.id')
            ->select('contratistas.proyecto_id', DB::raw('SUM(potc.valor) as total'))
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('potc.fecha', [$fechaInicioF, $fechaFinF]))
            ->where('potc.pagado', true) // Clave: Solo contamos lo que está marcado como pagado.
            ->whereNotNull('contratistas.proyecto_id')
            ->groupBy('contratistas.proyecto_id')
            ->pluck('total', 'proyecto_id');

        // Gasto C: Mano de Obra
        $gastosManoObra = DB::table('mano_obra')
            ->join('detalle_mano_obra', 'mano_obra.id', '=', 'detalle_mano_obra.mano_obra_id')
            ->select('mano_obra.proyecto_id', DB::raw('SUM(detalle_mano_obra.valor+detalle_mano_obra.adicional) as total'))
            ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                $q->where(fn($q) => $q->where('mano_obra.fecha_inicio', '<=', $fechaFinF)->where('mano_obra.fecha_fin', '>=', $fechaInicioF));
            })
            ->whereNotNull('mano_obra.proyecto_id')
            ->groupBy('mano_obra.proyecto_id')
            ->pluck('total', 'proyecto_id');

        $gastosPrestamos = DB::table('prestamos as p')
            ->join('catalogo_datos as cd', 'p.estado_id', '=', 'cd.id')
            ->join('proveedores as prov', 'p.trabajador_id', '=', 'prov.id')
            ->join('mano_obra as mo', 'prov.id', '=', 'mo.proyecto_id')
            ->select('mo.proyecto_id', DB::raw('SUM(p.monto) as total'))
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('p.fecha_solicitud', [$fechaInicioF, $fechaFinF]))
            ->where('cd.slug', 'estados.prestamos.pendiente')
            ->whereNotNull('mo.proyecto_id')
            ->groupBy('mo.proyecto_id')
            ->pluck('total', 'proyecto_id');

        // --- 3. PRE-CÁLCULO DE GASTOS ADMINISTRATIVOS (SIN PROYECTO) ---

        $gastoAdminAdquisiciones = DB::table('adquisiciones_detalle')
            ->join('adquisiciones', 'adquisiciones_detalle.adquisicion_id', '=', 'adquisiciones.id')
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('adquisiciones.fecha', [$fechaInicioF, $fechaFinF]))
            ->whereNull('adquisiciones.proyecto_id')
            ->sum(DB::raw('(adquisiciones_detalle.cantidad_solicitada * adquisiciones_detalle.valor) * (1 + (adquisiciones_detalle.iva / 100))'));

        // Nota: Si Contratistas o Mano de Obra también pueden ser administrativos, añade sus sumas aquí.
        // Por ahora, asumimos que solo Adquisiciones lo son, según tu descripción.
        $gastoAdministrativoTotal = $gastoAdminAdquisiciones;

        // Hacemos una consulta rápida a la otra DB para obtener el ID que necesitamos.
        // Usamos `value('id')` para obtener solo el valor del ID, es muy eficiente.
        $estadosEjecutadosIds = CatalogoDato::whereIn('slug', ['estados.proyectos.ejecucion', 'estados.proyectos.finalizado'])->pluck('id');

        // --- 4. OBTENER PROYECTOS Y CONSOLIDAR DATOS ---
        $proyectosQuery = self::query()
            ->when($proyectoInput, function ($q) use ($proyectoInput) {
                $q->where('id', $proyectoInput);
            });

        // Solo proyectos en estado "Ejecutado o Finalizado"
        if ($estadosEjecutadosIds->isNotEmpty()) {
            $proyectosQuery->whereIn('estado_id', $estadosEjecutadosIds);
        }
        // Si se pide un proyecto específico, no mostramos los gastos administrativos
        // para no confundir al usuario.
        if ($proyectoInput) {
            $gastoAdministrativoTotal = 0;
        }

        $proyectos = $proyectosQuery->get();

        $balance = $proyectos->map(function ($proyecto) use ($ingresosVentasPorProyecto, $ingresosProformasPorProyecto, $gastosAdquisiciones, $gastosContratistas, $gastosManoObra, $gastosPrestamos) {

            // Obtenemos los ingresos de ambas fuentes y los sumamos
            $ingresosVentas = $ingresosVentasPorProyecto->get($proyecto->id, 0);
            $ingresosProformas = $ingresosProformasPorProyecto->get($proyecto->id, 0);
            $totalIngresos = $ingresosVentas + $ingresosProformas;


            // Si SÍ se proporcionó un rango de fechas, lo evaluamos.
            /* if ($fechaInicioF && $fechaFinF) {
                // Creamos instancias de Carbon para una comparación segura y fácil.
                $inicio = Carbon::parse($fechaInicioF);
                $fin = Carbon::parse($fechaFinF);

                // Calculamos la diferencia en días.
                $diferenciaEnDias = $inicio->diffInDays($fin);

                // Si la diferencia es menor a un año (365 días), usamos el valor mensual.
                if ($diferenciaEnDias < 365) {
                    // Laravel llamará automáticamente a tu accesor getValorContratadoMensualAttribute()
                    $totalIngresos = $proyecto->valor_contratado_mensual;
                }
            }*/
            // Obtener los gastos pre-calculados, o 0 si no hay registros
            $gastoA = $gastosAdquisiciones->get($proyecto->id, 0);
            $gastoB = $gastosContratistas->get($proyecto->id, 0);
            $gastoC = $gastosManoObra->get($proyecto->id, 0);
            $gastoD = $gastosPrestamos->get($proyecto->id, 0);

            $totalGastos = $gastoA + $gastoB + $gastoC + $gastoD;
            $utilidad = $totalIngresos - $totalGastos;

            return [
                'proyecto_id' => $proyecto->id,
                'proyecto_nombre' => $proyecto->nombre_proyecto,
                'total_ingresos' => $totalIngresos,
                'total_gastos' => $totalGastos,
                'utilidad' => $utilidad,
            ];
        });

        // --- 5. AÑADIR LA FILA DE GASTOS ADMINISTRATIVOS AL REPORTE ---
        if ($gastoAdministrativoTotal > 0) {
            $balance->push([
                'proyecto_id' => 'admin', // Un identificador único
                'proyecto_nombre' => 'Gastos Administrativos (sin proyecto)',
                'total_ingresos' => 0,
                'total_gastos' => $gastoAdministrativoTotal,
                'utilidad' => -$gastoAdministrativoTotal, // La utilidad es una pérdida
            ]);
        }

        return $balance;
    }
}
