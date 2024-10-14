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
        <img src="data:image/png;base64,{{ $logo_base64 }}" alt="Logo">
        <div class="order-number">
            Orden de Recepción <br>{{ $orden['numero_pedido'] }}
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
                {{ $orden['proveedor'] }}<br>
            </div>

            <div class="right">
                <strong class="text-danger">FECHA:</strong> {{ $orden['fecha'] }}<br>
                <strong>TIPO:</strong> {{ strtoupper($orden['tipo']) }}<br>
                <strong>FORMA DE PAGO:</strong> {{ strtoupper($orden['forma_pago']) }}<br>
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
                        <th>Cantidad recibida</th>
                        <th>Unidad Medida</th>
                        <th>VALOR UNITARIO</th>
                        <th>Total</th>
                        <th>Necesidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orden['items'] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ strtoupper($item['producto']) }}</td>
                            <td>{{ $item['cantidad'] }}</td>
                            <td>{{ $item['cantidad_recibida'] }}</td>
                            <td>{{ strtoupper($item['unidad_medida']) }}</td>
                            <td>${{ $item['valor'] }}</td>
                            <td>${{ $item['total'] }}</td>
                            <td>{{ strtoupper($item['necesidad']) }}</td>
                        </tr>
                    @endforeach
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align: right">
                            <strong>TOTAL: </strong>
                        </td>
                        <td><strong>${{ $orden['total_orden'] }}</strong></td>
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
