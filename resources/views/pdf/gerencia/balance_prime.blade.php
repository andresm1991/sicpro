<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Orden de Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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

        .text-right {
            text-align: right;
        }

        .nombre-categoria {
            font-size: 14px;
            color: rgb(25, 25, 124);
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ logoBase64() }}" alt="Logo">
        <div class="order-number">
            Reporte balance {{ $tipoReporte }} <br>
            {{ date('Y-m-d') }}
        </div>
    </div>

    <div class="content">
        <div class="details">
            <div class="left">
                @if ($tipoReporte == 'Global')
                    <strong>Año: </strong> {{ $request->input('anio') ?? 'todos' }}<br>
                @else
                    <strong>Fecha: </strong> {{ $request->input('fechas') ?? 'todos' }}<br>
                @endif
                <strong>PROYECTO:</strong> {{ isset($proyecto) ? $proyecto->nombre_proyecto : 'todos' }}<br>
                <strong>subproyecto:</strong> {{ $request->subproyecto ?? 'n/a' }} <br>
            </div>

            <div class="rigth">

            </div>
            <div class="clear"></div>

        </div>

        <div class="items">
            {!! $vista !!}
        </div>
    </div>
</body>

</html>
