<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use App\Models\OrdenRecepcion;
use Illuminate\Http\Request;

class GenerarPdfController extends Controller
{
    public function generarPdfPedido(Adquisicion $pedido)
    {
        $informacion_empresa = CatalogoDato::getChildrenCatalogo('informacion.general');

        $cliente = [];
        foreach ($informacion_empresa as $info) {
            switch ($info->slug) {
                case 'informacion.general.nombre.empresa':
                    $cliente['nombre'] = $info->detalle;
                    break;
                case 'informacion.general.direccion':
                    $cliente['direccion'] = $info->detalle;
                    break;
                case 'informacion.general.telefono':
                    $cliente['telefono'] = $info->detalle;
                    break;
                case 'informacion.general.correo':
                    $cliente['correo'] = $info->detalle;
                    break;
            }
        }

        $items = [];
        foreach ($pedido->adquisiciones_detalle as $key => $detalle) {
            $items[] = [
                'producto' => $detalle->producto->descripcion,
                'cantidad' => $detalle->cantidad_solicitada,
                'necesidad' => $detalle->necesidad,
            ];
        }

        $orden = [
            'numero_pedido' => 'ORD-' . $pedido->numero,
            'fecha' => date('d-m-Y', strtotime($pedido->fecha)),

            'cliente' => $cliente,
            'items' => $items,
        ];
        //return view('pdf.adquiscion', compact('orden'));
        $pdf = PDF::loadView('pdf.adquiscion', compact('orden'));

        return $pdf->download('orden_pedido_' . $pedido->numero . '.pdf'); // para verl el pdf directamnete (stream), para descargar (download)

    }

    public function generarPdfRecepcion(Adquisicion $pedido)
    {
        $informacion_empresa = CatalogoDato::getChildrenCatalogo('informacion.general');
        $order_recepcion = OrdenRecepcion::where('adquisicion_id', $pedido->id)->first();
        if (!$order_recepcion) {
            return back()->with(
                'toast_error',
                'No se ha completado la orden de recepción, complétela para poder generar el archivo.',
            );
        }
        $cliente = [];
        foreach ($informacion_empresa as $info) {
            switch ($info->slug) {
                case 'informacion.general.nombre.empresa':
                    $cliente['nombre'] = $info->detalle;
                    break;
                case 'informacion.general.direccion':
                    $cliente['direccion'] = $info->detalle;
                    break;
                case 'informacion.general.telefono':
                    $cliente['telefono'] = $info->detalle;
                    break;
                case 'informacion.general.correo':
                    $cliente['correo'] = $info->detalle;
                    break;
            }
        }

        $items = [];
        foreach ($pedido->adquisiciones_detalle as $key => $detalle) {
            $items[] = [
                'producto' => $detalle->producto->descripcion,
                'cantidad' => $detalle->cantidad_solicitada,
                'cantidad_recibida' => $detalle->cantidad_recibida,
                'necesidad' => $detalle->necesidad,
            ];
        }

        $orden = [
            'numero_pedido' => 'ORD-' . $pedido->numero,
            'fecha' => date('d-m-Y', strtotime($pedido->fecha)),
            'proveedor' => $order_recepcion->proveedor->razon_social,
            'cliente' => $cliente,
            'forma_pago' => $order_recepcion->forma_pago->descripcion,
            'items' => $items,
        ];
        //return view('pdf.recepcion', compact('orden'));
        $pdf = PDF::loadView('pdf.recepcion', compact('orden'));

        return $pdf->download('orden_recepcion_' . $pedido->numero . '.pdf'); // para verl el pdf directamnete (stream), para descargar (download)


    }
}
