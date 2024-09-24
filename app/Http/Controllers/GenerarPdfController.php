<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\ManoObra;
use App\Models\Proveedor;
use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use App\Models\OrdenRecepcion;
use App\Models\DetalleManoObra;

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
        if (!isset($order_recepcion) || !$order_recepcion->completado) {
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

    public function planificacionManoObraPDF(ManoObra $mano_obra)
    {
        // Obtener todos los registros de mano de obra y agruparlos por proveedor y fechas
        $detalles = DetalleManoObra::with(['proveedor', 'articulo'])
            ->where('mano_obra_id', $mano_obra->id)
            ->get()
            ->groupBy('proveedor_id');

        $info_mano_obra = [
            'fecha' => dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin),
            'semana' => $mano_obra->semana,
        ];

        foreach ($detalles as $proveedor_id => $registros) {
            $semanas = [];

            // Inicializar la estructura de cada día de la semana (Lunes a Sábado)
            $diasSemana = ['L', 'M', 'M', 'J', 'V', 'S'];


            // Inicializa los valores para la fila del trabajador
            $fila = [
                'nombre' => '',
                'cargo' => '',
                'dias' => array_fill(0, 6, 0),   // Días de la semana en blanco
                'total_adicional' => 0,
                'total_descuento' => 0,
                'liquido_recibir' => 0,
                'observacion' => '',
            ];

            foreach ($registros as $detalle) {
                $articulo = $detalle->articulo;
                $proveedor = $detalle->proveedor;
                $fila['nombre'] = $proveedor->razon_social;
                $fila['cargo'] = $articulo->descripcion;
                // Convertir la fecha a día de la semana
                $diaSemana = \Carbon\Carbon::parse($detalle->fecha)->dayOfWeek;  // 0 = domingo, 1 = lunes, etc.

                // Si el día de la semana está dentro de los días válidos (lunes a viernes)
                if ($diaSemana >= 1 && $diaSemana <= 6) {
                    // Restamos 1 para alinear los días con el índice de la matriz (lunes es 0)
                    $fila['dias'][$diaSemana - 1] += $detalle->valor;
                }

                // Acumular adicionales y descuentos
                $fila['total_adicional'] += $detalle->adicional;
                $fila['total_descuento'] += $detalle->descuento;

                // Guardar la observación si la hay
                $fila['observacion'] = $detalle->observacion;
            }

            // Calcular el líquido a recibir
            $fila['liquido_recibir'] = ($fila['total_adicional'] + array_sum($fila['dias'])) - $fila['total_descuento'];

            // Añadir la fila al resultado final
            $info_mano_obra['detalle'][] = $fila;
        }

        //return $info_mano_obra;
        $pdf = PDF::loadView('pdf.mano_obra', compact('info_mano_obra'))->setPaper('a4', 'landscape');
        return $pdf->stream('reporte_mano_obra.pdf');
    }
}
