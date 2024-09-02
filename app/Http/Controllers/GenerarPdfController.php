<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Adquisicion;
use App\Models\CatalogoDato;
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

        $pdf = PDF::loadView('pdf.adquiscion', compact('orden'));

        return $pdf->download('orden_pedido.pdf'); // para verl el pdf directamnete (stream), para descargar (download)

        return view('pdf.adquiscion', compact('orden'));
    }
}
