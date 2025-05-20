<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Order de Pago Contratista</title>
    <style>
        /* Configuración general */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            text-transform: uppercase;
        }

        /* Contenedor principal */
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            /* Alinea todos los elementos verticalmente */
            justify-content: space-between;
            /* Distribuye espacio entre los elementos */
            margin-bottom: 20px;
        }

        /* Logo */
        .header img {
            float: left;
            max-width: 150px;
            height: 150px;
            vertical-align: middle;
            /* Asegura alineación precisa */
        }


        /* Contenido central */
        .header .content {
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-left: 20px;
        }

        .header .content small {
            display: block;
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        /* Detalles del proyecto */
        .details {
            min-width: 300px;
        }

        .details small {
            display: block;
            font-size: 12px;
            margin: 5px 0;
        }

        /* Tabla */
        #table-resumen {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        #table-resumen th,
        #table-resumen td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            background-color: #fefeff;
        }

        #table-resumen th {
            background-color: #d6dce9;
        }



        /* style table header */
        .table-header {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-header th,
        .table-header td {
            border: 0;
            background-color: transparent;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        ul {

            padding: 0 10px 0 10px;
        }

        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.2;
            background-position: center;
            background-size: contain;
            background-repeat: no-repeat;
        }

        .watermark img {
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Marca de agua -->
    <!-- Marca de agua -->
    <div class="watermark">
        <img src="{{ logoBase64() }}" alt="">
    </div>
    <div class="container">
        <table class="table-header">
            <tbody>
                <tr>
                    <td style="width: 1px;">
                        <img src="{{ logoBase64() }}" alt="Logo del Proyecto" width="150">
                    </td>
                    <td class="text-center">
                        <h2>Orden de Pago Nro. {{ $pago->numero_orden_pago }}</h2>
                        <h2></h2>
                    </td>
                </tr>
                <tr>
                    <td>
                        Fecha: {{ dateFormat('Y-m-d', 'd-m-Y', $pago->fecha) }}
                    </td>
                </tr>

            </tbody>
        </table>

        <!-- Tabla -->
        <table id="table-resumen">
            <thead>
                <tr>
                    <th>Contratista</th>
                    <th>Tipo de pago</th>
                    <th>Forma de pago</th>
                    <th>valor</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ $pago->contratista->proveedor->razon_social }}
                    </td>
                    <td>
                        {{ $pago->tipo_pago }}
                    </td>
                    <td>
                        {{ $pago->forma_pago }}
                    </td>
                    <td>
                        $ {{ number_format($pago->valor, 4) }}
                    </td>
                    <td>
                        {{ $pago->detalle }}
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</body>

</html>
