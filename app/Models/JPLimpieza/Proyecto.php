<?php

namespace App\Models\JPLimpieza;

use Carbon\Carbon;
use AWS\CRT\HTTP\Request;
use App\Models\CatalogoDato;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'proyectos';

    protected $fillable = [
        'nombre_proyecto',
        'entidad',
        'metros_contratado',
        'precio_por_metro',
        'tiempo_contratado',
        'archivo_portada',
        'archivo_orden_compra',
        'archivo_acta_final',
        'fecha_inicio',
        'fecha_finalizacion',
        'observacion',
        'telefono',
        'estado_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_finalizacion' => 'date',
    ];

    public function presupuesto()
    {
        return $this->hasMany(PresupuestoProyecto::class, 'proyecto_id');
    }

    public function adquisiciones()
    {
        return $this->hasMany(Adquisicion::class, 'proyecto_id');
    }

    public function manoObra()
    {
        return $this->hasMany(ManoObra::class, 'proyecto_id');
    }

    public function contratistas()
    {
        return $this->hasMany(Contratista::class, 'proyecto_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }


    public function getValorContratadoMensualAttribute()
    {
        return $this->precio_por_metro * $this->metros_contratado;
    }
    public function getTotalContratadoAttribute()
    {
        return $this->valor_contratado_mensual * $this->tiempo_contratado;
    }

    public function getValorContratadoMensualFormattedAttribute()
    {
        return number_format($this->valor_contratado_mensual, 4);
    }
    public function getTotalContratadoFormattedAttribute()
    {
        return number_format($this->total_contratado, 4);
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

        // Gasto A: Adquisiciones
        $gastosAdquisiciones = DB::connection('mysql_jp_limpieza')->table('detalle_adquisiciones')
            ->join('adquisiciones', 'detalle_adquisiciones.adquisicion_id', '=', 'adquisiciones.id')
            ->select('adquisiciones.proyecto_id', DB::raw('SUM((detalle_adquisiciones.cantidad * detalle_adquisiciones.precio_unitario) * (1 + (detalle_adquisiciones.iva / 100))) as total'))
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('adquisiciones.fecha', [$fechaInicioF, $fechaFinF]))
            ->whereNotNull('adquisiciones.proyecto_id') // Solo gastos asignados a proyectos
            ->groupBy('adquisiciones.proyecto_id')
            ->pluck('total', 'proyecto_id');

        // Gasto B: Contratistas
        $gastosContratistas = DB::connection('mysql_jp_limpieza')->table('contratistas')
            ->join('detalle_contratista', 'contratistas.id', '=', 'detalle_contratista.contratista_id')
            ->select('contratistas.proyecto_id', DB::raw('SUM(detalle_contratista.total) as total'))
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('contratistas.fecha', [$fechaInicioF, $fechaFinF]))
            ->whereNotNull('contratistas.proyecto_id')
            ->groupBy('contratistas.proyecto_id')
            ->pluck('total', 'proyecto_id');

        // Gasto C: Mano de Obra
        $gastosManoObra = DB::connection('mysql_jp_limpieza')->table('mano_obra')
            ->join('detalle_mano_obra', 'mano_obra.id', '=', 'detalle_mano_obra.mano_obra_id')
            ->select('mano_obra.proyecto_id', DB::raw('SUM(detalle_mano_obra.total_ingreso + detalle_mano_obra.aporte_patronal) as total'))
            ->when($fechaInicioF && $fechaFinF, function ($q) use ($fechaInicioF, $fechaFinF) {
                $q->where(fn($q) => $q->where('mano_obra.fecha_desde', '<=', $fechaFinF)->where('mano_obra.fecha_hasta', '>=', $fechaInicioF));
            })
            ->whereNotNull('mano_obra.proyecto_id')
            ->groupBy('mano_obra.proyecto_id')
            ->pluck('total', 'proyecto_id');

        // --- 3. PRE-CÁLCULO DE GASTOS ADMINISTRATIVOS (SIN PROYECTO) ---

        $gastoAdminAdquisiciones = DB::connection('mysql_jp_limpieza')->table('detalle_adquisiciones')
            ->join('adquisiciones', 'detalle_adquisiciones.adquisicion_id', '=', 'adquisiciones.id')
            ->when($fechaInicioF && $fechaFinF, fn($q) => $q->whereBetween('adquisiciones.fecha', [$fechaInicioF, $fechaFinF]))
            ->where('adquisiciones.administrativo', true) // La clave está aquí
            // Opcionalmente, puedes usar whereNull('proyecto_id') si es más seguro
            ->whereNull('adquisiciones.proyecto_id')
            ->sum(DB::raw('(detalle_adquisiciones.cantidad * detalle_adquisiciones.precio_unitario) * (1 + (detalle_adquisiciones.iva / 100))'));

        // Nota: Si Contratistas o Mano de Obra también pueden ser administrativos, añade sus sumas aquí.
        // Por ahora, asumimos que solo Adquisiciones lo son, según tu descripción.
        $gastoAdministrativoTotal = $gastoAdminAdquisiciones;

        // Hacemos una consulta rápida a la otra DB para obtener el ID que necesitamos.
        // Usamos `value('id')` para obtener solo el valor del ID, es muy eficiente.
        $estadoEjecutadoId = CatalogoDato::where('slug', 'estados.proyectos.ejecucion')->value('id');

        // --- 4. OBTENER PROYECTOS Y CONSOLIDAR DATOS ---
        $proyectosQuery = self::query()
            ->when($proyectoInput, function ($q) use ($proyectoInput) {
                $q->where('id', $proyectoInput);
            });

        // Solo proyectos en estado "Ejecutado"
        if ($estadoEjecutadoId) {
            $proyectosQuery->where('estado_id', $estadoEjecutadoId);
        }
        // Si se pide un proyecto específico, no mostramos los gastos administrativos
        // para no confundir al usuario.
        if ($proyectoInput) {
            $gastoAdministrativoTotal = 0;
        }

        $proyectos = $proyectosQuery->get();

        $balance = $proyectos->map(function ($proyecto) use ($gastosAdquisiciones, $gastosContratistas, $gastosManoObra, $fechaInicioF, $fechaFinF) {
            $totalIngresos = $proyecto->total_contratado;

            // Si SÍ se proporcionó un rango de fechas, lo evaluamos.
            if ($fechaInicioF && $fechaFinF) {
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
            }
            // Obtener los gastos pre-calculados, o 0 si no hay registros
            $gastoA = $gastosAdquisiciones->get($proyecto->id, 0);
            $gastoB = $gastosContratistas->get($proyecto->id, 0);
            $gastoC = $gastosManoObra->get($proyecto->id, 0);

            $totalGastos = $gastoA + $gastoB + $gastoC;
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
