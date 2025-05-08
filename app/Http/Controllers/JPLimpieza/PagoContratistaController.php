<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JPLimpieza\Proyecto;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Contratista;
use App\Models\JPLimpieza\PagoContratista;
use App\Http\Requests\JPLimpieza\PagoContratistaStoreRequest;

class PagoContratistaController extends Controller
{
    public function pagos($proyecto, $contratista)
    {
        $proyectoInfo = Proyecto::findOrFail($proyecto);
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => 'Contratistas', 'url' => route('jp.limpieza.contratistas.index', $proyecto)],
            ['name' => 'Pagos', 'url' => ''],
        ];

        $pagos = PagoContratista::where('contratista_id', $contratista)
            ->with(['contratista.proyecto', 'tipoPago', 'estado'])
            ->orderBy('fecha', 'desc')
            ->paginate(15);

        return view('jp_limpieza.contratistas.pagos.index', compact('pagos', 'proyecto', 'contratista', 'breadcrumbs'));
    }

    public function crear($proyecto, $contratista)
    {
        $proyectoInfo = Proyecto::findOrFail($proyecto);
        $contratista = Contratista::findOrFail($contratista);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => 'Pagos', 'url' => route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])],
            ['name' => 'Nuevo Pago', 'url' => ''],
        ];

        $pago = new PagoContratista();
        $numero = numeroPedido(PagoContratista::first());
        $formaPagos = CatalogoDato::getChildrenCatalogo('metodos.pagos');
        $estados = CatalogoDato::getChildrenCatalogo('estados.pagos.prestamos');

        return view('jp_limpieza.contratistas.pagos.create', compact('numero', 'proyecto', 'contratista', 'pago', 'formaPagos', 'estados', 'breadcrumbs'));
    }

    public function store(PagoContratistaStoreRequest $request, $proyecto, $contratista)
    {


        try {
            $infoContratista = Contratista::findOrFail($contratista);
            $monto = str_replace(',', '', $request->monto);
            $totalPendiente = $infoContratista->total_pendiente;

            if ($monto > $totalPendiente) {
                return redirect()->back()->with('error', 'El monto del pago no puede ser mayor al saldo pendiente.');
            }

            DB::beginTransaction();
            PagoContratista::create([
                'contratista_id' => $contratista,
                'monto' => $monto,
                'tipo_pago' => $request->tipo_pago,
                'forma_pago_id' => $request->forma_pago,
                'estado_id' => $request->estado,
                'fecha' => $request->fecha,
                'observaciones' => $request->detalle,
                'usuario_id' => auth()->user()->id,
            ]);

            DB::commit();
            return redirect()->route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])->with('success', 'Pago registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function edit($proyecto, $contratista, PagoContratista $pago)
    {
        $proyectoInfo = Proyecto::findOrFail($proyecto);
        $contratista = Contratista::findOrFail($contratista);


        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => 'Pagos Contratista', 'url' => route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])],
            ['name' => 'Editar Pago', 'url' => ''],
        ];

        $formaPagos = CatalogoDato::getChildrenCatalogo('metodos.pagos');
        $estados = CatalogoDato::getChildrenCatalogo('estados.pagos.prestamos');

        return view('jp_limpieza.contratistas.pagos.edit', compact('pago', 'proyecto', 'contratista', 'formaPagos', 'estados', 'breadcrumbs'));
    }

    public function update(PagoContratistaStoreRequest $request, $proyecto, $contratista, PagoContratista $pago)
    {
        try {
            DB::beginTransaction();
            $monto = str_replace(',', '', $request->monto);
            $totalPendiente = Contratista::findOrFail($contratista)->total_pendiente;

            // Verificar si el monto del pago es mayor al saldo pendiente
            if ($monto > $totalPendiente) {
                return redirect()->back()->with('error', 'El monto del pago no puede ser mayor al saldo pendiente.');
            }

            if ($pago->estado_id == CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pagado')) {
                return redirect()->back()->with('error', 'El pago ya fue registrado y no es posible modificarlo.');
            }

            $pago->update([
                'monto' => $monto,
                'tipo_pago' => $request->tipo_pago,
                'forma_pago_id' => $request->forma_pago,
                'estado_id' => $request->estado,
                'fecha' => $request->fecha,
                'observaciones' => $request->detalle,
            ]);

            DB::commit();
            return redirect()->route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])->with('success', 'Pago actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar el pago: ' . $e->getMessage());
        }
    }
}
