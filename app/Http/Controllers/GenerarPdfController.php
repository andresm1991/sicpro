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
            ->orderBy('fecha', 'asc')
            ->get();
        $agrupados = $detalles->groupBy('proveedor_id');
        
        $info_mano_obra = [
            'proyecto' => $mano_obra->proyecto->nombre_proyecto,
            'fecha' => dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin),
            'semana' => $mano_obra->semana,
            'detalle' => []  // Para almacenar los detalles procesados
        ];
    
        foreach ($agrupados as $proveedor_id => $registros_por_proveedor) {
            $nombre_mostrado = false;  // Bandera para saber si ya mostramos el nombre del proveedor
    
            foreach ($registros_por_proveedor->groupBy('articulo_id') as $articulo_id => $registros) {
                // Inicializamos las variables para cada trabajador y su cargo
                $fila = [
                    'nombre' => '',
                    'cargo' => '',
                    'dias' => array_fill(0, 6, 0),   // Días de la semana en blanco (Lunes a Sábado)
                    'total_adicional' => 0,
                    'total' => 0,
                    'total_descuento' => 0,
                    'liquido_recibir' => 0,
                    'observacion' => [],
                    'detalle_adicional' => [],
                    'detalle_descuento' => [],
                ];
    
                // Iteramos los registros de cada proveedor y cargo
                foreach ($registros as $detalle) {
                    $articulo = $detalle->articulo;
                    $proveedor = $detalle->proveedor;
    
                    $fila['nombre'] = $proveedor->razon_social;
                    // El cargo puede cambiar por artículo
                    $fila['cargo'] = $articulo->descripcion;  
    
                    // Convertimos la fecha a día de la semana (1 = Lunes, 2 = Martes, etc.)
                    $diaSemana = Carbon::parse($detalle->fecha)->dayOfWeek;  // 0 = Domingo, 1 = Lunes, etc.
    
                    // Si el día de la semana está entre Lunes y Sábado
                    if ($diaSemana >= 1 && $diaSemana <= 6) {
                        // Restamos 1 a `diaSemana` para ajustar al índice (Lunes = 0, Sábado = 5)
                        $fila['dias'][$diaSemana - 1] += $detalle->valor;
                    }
    
                    // Acumulamos los totales
                    $fila['total'] += $detalle->valor + $detalle->adicional;
                    $fila['total_adicional'] += $detalle->adicional;
                    $fila['total_descuento'] += $detalle->descuento;
                    if($detalle->detalle_adicional){
                        $fila['detalle_adicional'][]= $detalle->detalle_adicional;
                    }
                    if($detalle->detalle_descuento){
                        $fila['detalle_descuento'][]= $detalle->detalle_descuento;
                    }
                    
                    
    
                    // Si existe una observación, la agregamos
                    if (!empty($detalle->observacion)) {
                        $fila['observacion'][] = $detalle->observacion;  // Concatenamos las observaciones
                    }
                }
    
                // Calculamos el líquido a recibir
                $fila['liquido_recibir'] = ($fila['total_adicional'] + array_sum($fila['dias'])) - $fila['total_descuento'];
    
                // Añadimos la fila al array de resultados
                $info_mano_obra['detalle'][] = $fila;
    
                // Para las siguientes filas del mismo proveedor, dejamos el nombre en blanco
                $nombre_mostrado = true;
            }
        }
        $logo_base64 = base64_encode(file_get_contents(public_path('images/logo_empresa.jpg')));

        $pdf = PDF::loadView('pdf.mano_obra', compact('info_mano_obra', 'logo_base64'))->setPaper('a3', 'landscape');
        return $pdf->download('reporte_mano_obra.pdf');
    }
}
