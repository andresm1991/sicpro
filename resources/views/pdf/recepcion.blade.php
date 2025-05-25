<!-- resources/views/pdf/orden_pedido.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Orden de Recepción</title>

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
        }

        .details .right {
            /* text-align: right;*/
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
        <img src="{{ logoBase64() }}" alt="Logo">
        <div class="order-number">
            Adquisición Nro. {{ $orden['numero_pedido'] }}<br>
            Semana: {{ $orden['semana'] }}
        </div>
    </div>

    <div class="content">
        <div class="details">
            <div class="left">
                <strong>PROYECTO:</strong>
                {{ strtoupper($orden['proyecto']) }}<br>
                <strong>ETAPA:</strong>
                {{ strtoupper($orden['etapa']) }}<br>
                <strong>PROVEEDOR:</strong>
                {{ strtoupper($orden['proveedor']) }}<br>
            </div>

            <div class="right">
                <strong class="text-danger">FECHA:</strong> {{ $orden['fecha'] }}<br>
                <strong>TIPO:</strong> {{ strtoupper($orden['tipo']) }}<br>
                <strong>FORMA DE PAGO:</strong> {{ strtoupper($orden['forma_pago']) }}<br>
                @if (strtoupper($orden['estado_pedido']) == strtoupper('Completado'))
                    <strong>FACTURA:</strong> {{ strtoupper($orden['factura']) }}<br>
                @endif
            </div>
            <div class="clear"></div>

        </div>

        <div class="items">
            @php
                $producto = array_column($orden['items'], 'producto');
                $key = array_search('gasolina para camioneta', $producto);

            @endphp

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        @if (strtoupper($orden['tipo']) == 'SERVICIOS' || strtoupper($orden['estado_pedido']) == strtoupper('Completado'))
                            <th>Unidad Medida</th>
                            <th>Valor unitario</th>
                            @if (strtoupper($orden['estado_pedido']) == strtoupper('Completado'))
                                <th>Iva</th>
                            @endif
                            <th>Total</th>
                        @endif

                        @if ($key !== false)
                            <th>KM</th>
                        @endif
                        <th>Necesidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orden['items'] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ strtoupper($item['producto']) }}</td>
                            <td>{{ $item['cantidad'] }}</td>
                            @if (strtoupper($orden['tipo']) == 'SERVICIOS' || strtoupper($orden['estado_pedido']) == strtoupper('Completado'))
                                <td>{{ strtoupper($item['unidad_medida']) }}</td>
                                <td>${{ number_format($item['valor'], 4) }}</td>
                                @if (strtoupper($orden['estado_pedido']) == strtoupper('Completado'))
                                    <td>{{ $item['iva'] }}</td>
                                @endif
                                <td>${{ number_format($item['total'], 4) }}</td>
                            @endif
                            @if ($key !== false)
                                <td>{{ $item['kilometraje'] }}</td>
                            @endif
                            <td>{{ strtoupper($item['necesidad']) }}</td>
                        </tr>
                    @endforeach
                    @if (strtoupper($orden['tipo']) == 'SERVICIOS' || strtoupper($orden['estado_pedido']) == strtoupper('Completado'))
                <tfoot>
                    <tr>
                        <td colspan="{{ strtoupper($orden['estado_pedido']) == strtoupper('Completado') ? 6 : (strtoupper($orden['tipo']) == 'SERVICIOS' ? 5 : 6) }}"
                            style="text-align: right">
                            <strong>TOTAL GENERAL: </strong>
                        </td>
                        <td><strong>${{ number_format($orden['total_orden'], 4) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif

                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Esta es un documento generado electrónicamente. No requiere firma.
    </div>
</body>

</html>
