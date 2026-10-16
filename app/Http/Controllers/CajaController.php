<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use App\Services\CajaService;
use App\Models\MovimientoCaja;
use App\Services\LogService;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use Illuminate\Pagination\LengthAwarePaginator;

class CajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => route('administrativo.index')],
            ['name' => 'Caja', 'url' => ''],
        ];

        $movimientosAsc = MovimientoCaja::orderBy('id', 'asc')->get();
        $saldo = 0;
        foreach ($movimientosAsc as $movimiento) {
            if ($movimiento->tipo == 'ingreso') {
                $saldo += $movimiento->monto;
            } else {
                $saldo -= $movimiento->monto;
            }
            $movimiento->saldo_acumulado = $saldo;
        }

        $movimientos = $movimientosAsc->sortByDesc('id')->values();
        $page = request()->get('page', 1);
        $perPage = 15;
        $items = $movimientos;
        $paginated = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('administrativo.cajas.index', ['movimientos' => $paginated, 'breadcrumbs' => $breadcrumbs]);
    }

    public function guardarMovimiento(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();

                $movimiento = MovimientoCaja::registrarMovimiento($request);

                LogService::auditar(
                    'crear',
                    'movimiento_caja',
                    $movimiento->id,
                    sprintf(
                        'Se registró un %s de caja por $%s%s',
                        $movimiento->tipo,
                        number_format((float) $movimiento->monto, 2),
                        $movimiento->descripcion ? " ({$movimiento->descripcion})" : ''
                    ),
                    [],
                    [
                        'tipo' => $movimiento->tipo,
                        'monto' => (float) $movimiento->monto,
                        'descripcion' => $movimiento->descripcion,
                        'fecha' => $movimiento->fecha ? $movimiento->fecha->format('Y-m-d') : null,
                        'origen_type' => $movimiento->origen_type,
                        'origen_id' => $movimiento->origen_id,
                    ]
                );

                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }
    public function eliminarMovimiento($id)
    {
        if (request()->ajax()) {
            try {
                DB::beginTransaction();

                $movimiento = MovimientoCaja::find($id);
                if (!empty($movimiento->adquisicion)) {
                    return response()->json(['success' => false, 'message' => 'No se puede eliminar un movimiento que tenga una adquisicion.']);
                } else {
                    LogService::auditar(
                        'eliminar',
                        'movimiento_caja',
                        $movimiento->id,
                        sprintf(
                            'Se eliminó un %s de caja por $%s%s',
                            $movimiento->tipo,
                            number_format((float) $movimiento->monto, 2),
                            $movimiento->descripcion ? " ({$movimiento->descripcion})" : ''
                        ),
                        $movimiento->toArray()
                    );

                    MovimientoCaja::destroy($id);
                    DB::commit();
                    return response()->json(['success' => true, 'message' => MessagesConstant::DELETE]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }
}
