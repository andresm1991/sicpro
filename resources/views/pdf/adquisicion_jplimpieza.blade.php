<!-- resources/views/pdf/orden_pedido.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Orden de Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            text-transform: uppercase;
        }

        .header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            /* Centrar verticalmente el contenido */
            position: relative;
        }

        .header img {
            max-width: 150px;
            height: auto;
        }

        .header .order-number {
            flex-grow: 1;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .content {
            margin: 0 auto;
            width: 100%;
        }

        .details {
            margin-bottom: 20px;
        }

        .details .left,
        .details .right {
            width: 50%;
            float: left;
            margin-right: 10px;
        }

        .details .right {
            text-align: right;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .clear {
            clear: both;
        }

        .items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .items th,
        .items td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .items th {
            background-color: #f2f2f2;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ logoBase64('jp') }}" alt="Logo">
        <div class="order-number">
            Orden de Pedido <br>{{ $adquisicion->numero }}
        </div>
    </div>

    <div class="content">
        <div class="details">
            <div class="left">
                <strong>PROYECTO:</strong>
                {{ isset($adquisicion->proyecto->nombre_proyecto) ? strtoupper($adquisicion->proyecto->nombre_proyecto) : 'GENERAL' }}<br>
                <strong>entidad:</strong>
                {{ isset($adquisicion->proyecto->entidad) ? strtoupper($adquisicion->proyecto->entidad) : 'N/A' }}<br>
            </div>

            <div class="right">
                <strong class="text-danger">FECHA:</strong> {{ $adquisicion->fecha_formateada }}<br>
                <strong>FORMA DE PAGO:</strong> {{ strtoupper($adquisicion->formaPago->descripcion) }}<br>
                @if (strtoupper($adquisicion->estado) == strtoupper('Completado'))
                    <strong>FACTURA:</strong> {{ strtoupper($adquisicion->nro_factura) }}<br>
                @endif
            </div>
            <div class="clear"></div>
        </div>

        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Unidad Medida</th>
                        <th>Valor unitario</th>
                        <th>Iva</th>
                        <th>Total</th>
                        <th>Necesidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($adquisicion->detalles as $index => $detalle)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ strtoupper($detalle->producto->descripcion) }}</td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>{{ strtoupper($detalle->unidadMedida->descripcion) }}</td>
                            <td>${{ number_format($detalle->precio_unitario, 4) }}</td>
                            <td>{{ $detalle->iva }}</td>
                            <td>${{ number_format($detalle->total_formatted_con_iva, 4) }}</td>
                            <td>{{ $detalle->necesidad }}</td>
                        </tr>
                    @endforeach
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: right">
                            <strong>TOTAL GENERAL: </strong>
                        </td>
                        <td><strong>${{ number_format($adquisicion->detalles->sum('total_formatted_con_iva'), 4) }}</strong>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>


                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Esta es una orden de pedido generada electrónicamente. No requiere firma.
    </div>
</body>

</html>
