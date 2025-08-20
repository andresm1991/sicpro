<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de solicitudes</title>
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
        #table-resumen th:first-child,
        #table-resumen td:first-child {
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
        <img src="{{ logoBase64($tipo) }}" />
    </div>
    <div class="container">
        <table class="table-header">
            <tbody>
                <tr>
                    <td style="width: 1px;">
                        <img src="{{ logoBase64($tipo) }}" alt="Logo del Proyecto" width="150">
                    </td>
                    <td class="text-center">
                        <h2>Reporte de Caja</h2>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        {{ $query['fecha_reporte'] }}
                    </td>
                </tr>
            </tbody>
        </table>

        <table id="table-resumen">
            <tr>
                <th scope="col">fecha</th>
                <th scope="col">proyecto</th>
                <th scope="col">subproyecto</th>
                <th scope="col">descripción</th>
                <th scope="col">necesidad</th>
                <th scope="col">proveedor</th>
                <th scope="col">documento</th>
                <th scope="col">ingreso</th>
                <th scope="col">egreso</th>
                <th scope="col">saldo</th>
            </tr>
            <tbody>
                <tr>
                    <td class="aling-middle bg-secondary text-white" colspan="9" style="background-color: #d2d5da">
                        <strong>{{ $query['fecha_saldo_inicio'] }}</strong>
                    </td>
                    <td class="aling-middle bg-secondary text-white" style="background-color: #d2d5da">
                        <strong>${{ $query['saldo_inicial'] }}</strong>
                    </td>
                </tr>
                @forelse ($query['movimientos'] as $movimiento)
                    <tr>
                        <td>
                            {{ $movimiento['fecha_formateada'] }}
                        </td>
                        <td class="align-middle">
                            {{ isset($movimiento['origen_id']) &&
                            ((isset($movimiento['origen_type']) && $movimiento['origen_type'] == 'App\Models\Adquisicion') ||
                                (isset($movimiento['origen_type']) && $movimiento['origen_type'] == 'adquisicion_jplimpieza')) &&
                            isset($movimiento['adquisicion']['proyecto']['nombre_proyecto']) &&
                            !empty($movimiento['adquisicion']['proyecto']['nombre_proyecto'])
                                ? $movimiento['adquisicion']['proyecto']['nombre_proyecto']
                                : '-' }}
                        </td>
                        <td class="align-middle">
                            {{ isset($movimiento['origen_id']) &&
                            isset($movimiento['adquisicion']['subproyecto']) &&
                            !empty($movimiento['adquisicion']['subproyecto'])
                                ? $movimiento['adquisicion']['subproyecto']
                                : '-' }}
                        </td>
                        <td>
                            {{ isset($movimiento['articulo_id']) ? $movimiento['articulo']['descripcion'] : (isset($movimiento['producto_id']) ? $movimiento['producto']['nombre'] : $movimiento['descripcion']) }}
                        </td>
                        <td>
                            {{ isset($movimiento['articulo_id']) || isset($movimiento['producto_id']) ? $movimiento['descripcion'] : '-' }}
                        </td>
                        <td>
                            {{ isset($movimiento['proveedor']) ? $movimiento['proveedor']['razon_social'] : '-' }}
                        </td>
                        <td>
                            {{ isset($movimiento['referencia']) ? $movimiento['referencia'] : '-' }}
                        </td>
                        <td>
                            {{ $movimiento['tipo'] == 'ingreso' ? $movimiento['monto_formatted'] : '-' }}
                        </td>
                        <td>
                            {{ $movimiento['tipo'] == 'egreso' ? $movimiento['monto_formatted'] : '-' }}
                        </td>
                        <td>${{ $movimiento['saldo_acumulado'] }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="aling-middle">
                        <strong>Sumas totales</strong>
                    </td>
                    <td class="aling-middle">
                        <strong>${{ $query['total_ingresos'] }}</strong>
                    </td>
                    <td class="aling-middle">
                        <strong>${{ $query['total_egresos'] }}</strong>
                    </td>
                    <td class="aling-middle">
                        -
                    </td>
                </tr>
                <tr>
                    <td class="aling-middle" colspan="9">
                        <strong>{{ $query['fecha_saldo_fin'] }}</strong>
                    </td>
                    <td class"aling-middle">
                        <strong>${{ $query['saldo_final'] }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>
