<?php

namespace App\Http\Controllers\JPLimpieza;

use Illuminate\Http\Request;
use App\Models\JPLimpieza\Caja;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class CajaController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'JPLimpieza', 'url' => route('jp.limpieza.index')],
            ['name' => 'Caja', 'url' => ''],
        ];

        $movimientosAsc = Caja::orderBy('id', 'asc')->get();
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

        return view('jp_limpieza.caja.index', ['movimientos' => $paginated, 'breadcrumbs' => $breadcrumbs]);
    }

    public function guardarMovimiento(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();

                Caja::registrarMovimiento($request);

                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }
}
