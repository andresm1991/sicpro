<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use App\Services\CajaService;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use Illuminate\Pagination\LengthAwarePaginator;

class CajaController extends Controller
{
    protected $cajaService;

    public function __construct(CajaService $cajaService)
    {
        $this->cajaService = $cajaService;
    }

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

                MovimientoCaja::registrarMovimiento($request);

                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }
}
