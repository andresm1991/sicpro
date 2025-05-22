<?php

namespace App\Http\Controllers\JPLimpieza;

use Illuminate\Http\Request;
use App\Models\JPLimpieza\Proyecto;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\DetalleManoObra;
use App\Models\JPLimpieza\ManoObra;
use Illuminate\Support\Facades\DB;

class ManoObraController extends Controller
{
    public function index(Proyecto $proyecto)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('jp.limpieza.proyectos.show', $proyecto->id)],
            ['name' => 'Mano de obra', 'url' => ''],
        ];

        $mano_obras = ManoObra::where('proyecto_id', $proyecto->id)->orderBy('created_at', 'desc')->paginate(15);

        return view('jp_limpieza.mano_obra.index', compact('breadcrumbs', 'proyecto', 'mano_obras'));
    }

    public function create(Proyecto $proyecto)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('jp.limpieza.proyectos.show', $proyecto->id)],
            ['name' => 'Mano de obra', 'url' => route('jp.limpieza.mano.obra.index', $proyecto->id)],
            ['name' => 'Nueva planificacion', 'url' => ''],
        ];

        $planificacion = new ManoObra();
        // Obtener la última ManoObra para este proyecto
        $ultimaManoObra = ManoObra::where('proyecto_id', $proyecto->id)
            ->latest()
            ->first();

        // Obtener los detalles asociados
        $ultimosDetalles = $ultimaManoObra
            ? $ultimaManoObra->detalles
            : collect();

        return view('jp_limpieza.mano_obra.create', compact('breadcrumbs', 'proyecto', 'planificacion', 'ultimosDetalles'));
    }

    public function store(Request $request, Proyecto $proyecto)
    {
        try {
            DB::beginTransaction();
            $fecha_desde = $request->input('fecha_desde');
            $fecha_hasta = $request->input('fecha_hasta');
            $tipo = $request->input('tipo_plantilla');



            $items = array_map(function ($proveedor, $sueldo, $hExtras, $totalGanado, $fondos, $dTercero, $dCuarto, $totalIngresos, $iess, $atrasos, $anticipos, $prestamoIess, $quincena, $prestamoJP, $totalDescuentos, $totalRecibir) {
                return [
                    'proveedor' => $proveedor,
                    'sueldo' => $sueldo,
                    'h_extras' => $hExtras,
                    'total_ganado' => $totalGanado,
                    'fondos' => $fondos,
                    'decimo_tercero' => $dTercero,
                    'decimo_cuarto' => $dCuarto,
                    'total_ingresos' => $totalIngresos,
                    'iess' => $iess,
                    'atrasos_faltas' => $atrasos,
                    'anticipos' => $anticipos,
                    'prestamo_iess' => $prestamoIess,
                    'quincena' => $quincena,
                    'prestamo_jp' => $prestamoJP,
                    'total_descuentos' => $totalDescuentos,
                    'total_recibir' => $totalRecibir,
                ];
            }, $request->proveedor, $request->sueldo, $request->h_extras, $request->total_ganado, $request->fondos, $request->decimo_tercero, $request->decimo_cuarto, $request->total_ingresos, $request->iess, $request->atrasos_faltas, $request->anticipos, $request->prestamo_iess, $request->quincena, $request->prestamo_jp, $request->total_descuentos, $request->total_recibir);

            $mano_obra = ManoObra::create([
                'proyecto_id' => $proyecto->id,
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta,
                'tipo' => strtoupper($tipo),
            ])->id;

            if ($mano_obra) {
                foreach ($items as $item) {
                    DetalleManoObra::create([
                        'mano_obra_id' => $mano_obra,
                        'proveedor_id' => $item['proveedor'],
                        'sueldo' => $item['sueldo'],
                        'horas_extras' => $item['h_extras'],
                        'total_ganado' => $item['total_ganado'],
                        'fondos' => $item['fondos'],
                        'decimo_tercero' => $item['decimo_tercero'],
                        'decimo_cuarto' => $item['decimo_cuarto'],
                        'total_ingreso' => $item['total_ingresos'],
                        'iess' => $item['iess'],
                        'atrasos_faltas' => $item['atrasos_faltas'],
                        'anticipos' => $item['anticipos'],
                        'prestamo_iess' => $item['prestamo_iess'],
                        'quincena' => $item['quincena'],
                        'prestamo_jp' => $item['prestamo_jp'],
                        'total_descuentos' => $item['total_descuentos'],
                        'total_recibir' => $item['total_recibir'],
                    ]);
                }
                DB::commit();
                return redirect()->route('jp.limpieza.mano.obra.index', $proyecto->id)->with('success', 'Planificacion creada exitosamente.');
            }

            throw new \Exception('el proceso no se completó correctamente, por favor intente nuevamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrio un error inesperado: ' . $e->getMessage());
        }
    }

    public function edit(Proyecto $proyecto, ManoObra $mano_obra)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('jp.limpieza.proyectos.show', $proyecto->id)],
            ['name' => 'Mano de obra', 'url' => route('jp.limpieza.mano.obra.index', $proyecto->id)],
            ['name' => 'Editar planificacion', 'url' => ''],
        ];

        $planificacion = $mano_obra;
        $ultimosDetalles = $planificacion->detalles;

        return view('jp_limpieza.mano_obra.edit', compact('breadcrumbs', 'proyecto', 'planificacion', 'ultimosDetalles'));
    }


    public function update(Request $request, Proyecto $proyecto, ManoObra $mano_obra)
    {
        try {
            DB::beginTransaction();
            $fecha_desde = $request->fecha_desde;
            $fecha_hasta = $request->fecha_hasta;

            $personalExistente = DetalleManoObra::where('mano_obra_id', $mano_obra->id)->pluck('proveedor_id')->toArray();
            $personalEliminar = array_diff($personalExistente, $request->input('proveedor', []));

            // Eliminar los registros de personal
            if (!empty($personalEliminar)) {
                DetalleManoObra::whereIn('proveedor_id', $personalEliminar)->delete();
            }

            $items = array_map(function ($proveedor, $sueldo, $hExtras, $totalGanado, $fondos, $dTercero, $dCuarto, $totalIngresos, $iess, $atrasos, $anticipos, $prestamoIess, $quincena, $prestamoJP, $totalDescuentos, $totalRecibir) {
                return [
                    'proveedor' => $proveedor,
                    'sueldo' => $sueldo,
                    'h_extras' => $hExtras,
                    'total_ganado' => $totalGanado,
                    'fondos' => $fondos,
                    'decimo_tercero' => $dTercero,
                    'decimo_cuarto' => $dCuarto,
                    'total_ingresos' => $totalIngresos,
                    'iess' => $iess,
                    'atrasos_faltas' => $atrasos,
                    'anticipos' => $anticipos,
                    'prestamo_iess' => $prestamoIess,
                    'quincena' => $quincena,
                    'prestamo_jp' => $prestamoJP,
                    'total_descuentos' => $totalDescuentos,
                    'total_recibir' => $totalRecibir,
                ];
            }, $request->proveedor, $request->sueldo, $request->h_extras, $request->total_ganado, $request->fondos, $request->decimo_tercero, $request->decimo_cuarto, $request->total_ingresos, $request->iess, $request->atrasos_faltas, $request->anticipos, $request->prestamo_iess, $request->quincena, $request->prestamo_jp, $request->total_descuentos, $request->total_recibir);

            $mano_obra->fecha_desde = $fecha_desde;
            $mano_obra->fecha_hasta = $fecha_hasta;
            $mano_obra->save();

            foreach ($items as $item) {
                DetalleManoObra::updateOrCreate(
                    [
                        'mano_obra_id' => $mano_obra->id,
                        'proveedor_id' => $item['proveedor'],
                    ],
                    [
                        'sueldo' => $item['sueldo'],
                        'horas_extras' => $item['h_extras'],
                        'total_ganado' => $item['total_ganado'],
                        'fondos' => $item['fondos'],
                        'decimo_tercero' => $item['decimo_tercero'],
                        'decimo_cuarto' => $item['decimo_cuarto'],
                        'total_ingreso' => $item['total_ingresos'],
                        'iess' => $item['iess'],
                        'atrasos_faltas' => $item['atrasos_faltas'],
                        'anticipos' => $item['anticipos'],
                        'prestamo_iess' => $item['prestamo_iess'],
                        'quincena' => $item['quincena'],
                        'prestamo_jp' => $item['prestamo_jp'],
                        'total_descuentos' => $item['total_descuentos'],
                        'total_recibir' => $item['total_recibir'],
                    ],
                );
            }
            DB::commit();

            return redirect()->route('jp.limpieza.mano.obra.edit', [$proyecto->id, $mano_obra->id])->with('success', 'Planificacion actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrio un error inesperado: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Proyecto $proyecto, ManoObra $mano_obra)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                if ($mano_obra->delete()) {
                    DB::commit();
                    return response()->json(['success' => true, 'message' => 'Planificacion eliminada exitosamente.']);
                } else {
                    throw new \Exception('No se pudo eliminar la planificacion.');
                    DB::rollBack();
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Ocurrio un error inesperado: ' . $e->getMessage()]);
            }
        }
    }
}
