<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de adquisiciones</title>
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
        .tabla-actividades {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tabla-actividades th,
        .tabla-actividades td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            background-color: #fefeff;
        }

        .tabla-actividades th {
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

        /* Ajustar el ancho de la primera columna */
        #table-resumen th:last-child,
        #table-resumen td:last-child {
            width: 100px;
            /* Ancho fijo para la primera columna */
            min-width: 100px;
            /* Evita que se achique más allá de este valor */
            max-width: 100px;
            /* Evita que se expanda más allá de este valor */
        }

        /* Estilos para la marca de agua */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.2;
            /* Usar la imagen como fondo */
            background-position: center;
            background-size: cover;
            /* Ajustar la imagen para cubrir todo el fondo */
            background-repeat: no-repeat;
        }

        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Ajustar la imagen sin distorsión */
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
            opacity: 0.2;
        }

        .bg-success {
            background-color: #28a745 !important;
        }
    </style>
</head>

<body>
    <!-- Marca de agua -->
    <div class="watermark">
        <img src="{{ logoBase64() }}" />
    </div>
    <div class="container">
        <table class="table-header">
            <tbody>
                <tr>
                    <td style="width: 1px;">
                        <img src="{{ logoBase64() }}" alt="Logo del Proyecto" width="150">
                    </td>
                    <td class="text-center">
                        <h2>Reporte de adquisiciones</h2>
                    </td>
                </tr>
                @if ($proveedor != '')
                    <tr>
                        <td colspan="2">
                            <strong>proveedor:
                                {{ isset($proveedor->razon_social) ? $proveedor->razon_social : $proveedor->apellidos . ' ' . $proveedor->nombres }}</strong>
                        </td>
                    </tr>
                @endif
                @if ($producto != '')
                    <tr>
                        <td colspan="2">
                            <strong>Producto: {{ $producto->descripcion }}</strong>
                        </td>
                    </tr>
                @endif

                @if ($fechas != '')
                    <tr>
                        <td colspan="2">
                            <strong>Fecha: {{ $fechas }}</strong>
                        </td>
                    </tr>
                @endif

            </tbody>
        </table>

        <table id="table-resumen">
            <tr>
                <th scope="col">fecha</th>
                <th scope="col">proyecto</th>
                <th scope="col">proveedor</th>
                <th scope="col">producto</th>
                <th scope="col">plazo semanas</th>
                <th scope="col">estapa</th>
                <th scope="col">estado</th>
                <th scope="col">total</th>
                <th scope="col">abonado</th>
                <th scope="col">saldo</th>
            </tr>
            <tbody>
                @forelse ($query as $items)
                    <tr>
                        <td>{{ $items['contratista']->fecha }}</td>
                        <td>{{ $items['contratista']->proyecto->nombre_proyecto }}</td>
                        <td>{{ isset($items['contratista']->proveedor->razon_social) ? $items['contratista']->proveedor->razon_social : $items['contratista']->proveedor->apellidos . ' ' . $items['contratista']->proveedor->nombres }}
                        </td>
                        <td>{{ $items['contratista']->articulo->descripcion }}</td>
                        <td>{{ $items['contratista']->plazo_semanas }}</td>
                        <td>{{ $items['contratista']->etapa->descripcion }}</td>
                        <td>{{ $items['contratista']->estado->descripcion }}</td>
                        <td>{{ $items['total'] }}</td>
                        <td>{{ $items['total_pagado'] }}</td>
                        <td>{{ $items['saldo'] }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>

        <h2 class="text-right">Total General: <strong>
                ${{ number_format($totalGeneral, 4) }}</strong></h2>
        <h2 class="text-right">Total Pagado: <strong>
                ${{ number_format($totalPagado, 4) }}</strong></h2>
        <h2 class="text-right">Total Saldo: <strong>
                ${{ number_format($totalSaldos, 4) }}</strong></h2>

    </div>
</body>

</html>
