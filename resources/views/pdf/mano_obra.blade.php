<!-- resources/views/pdf/orden_pedido.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte Mano Obra</title>

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
    <div class="content">
        <div class="details">
            <div class="left">
                <strong class="text-danger">Fecha:</strong> {{ $info_mano_obra['fecha'] }}<br>
                <strong class="text-danger">Semana:</strong> {{ $info_mano_obra['semana'] }}<br>
            </div>

            <div class="clear"></div>

        </div>

        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Apellidos y Nombres</th>
                        <th>Cargo</th>
                        <th>L</th>
                        <th>M</th>
                        <th>M</th>
                        <th>J</th>
                        <th>V</th>
                        <th>Adicionales</th>
                        <th>TOTAL</th>
                        <th>Descuento</th>
                        <th>Liquido a Recibir</th>
                        <th>Observaciones</th>
                        <th>Firma</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Esta es una orden de pedido generada electrónicamente. No requiere firma.
    </div>
</body>

</html>
