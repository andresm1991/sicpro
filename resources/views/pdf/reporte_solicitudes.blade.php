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

        @if ($tipo_solicitud != 'reposiciones_global' && $tipo_solicitud != 'reposiciones_detallado')
            #table-resumen th:nth-child(2),
            #table-resumen td:nth-child(2) {
                width: 50px;
            }
        @endif

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
                        <h2>Reporte de Solicitudes</h2>
                        <h4>{{ str_replace('_', ' ', $tipo_solicitud) }}</h4>
                    </td>
                </tr>

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
                <th>colaborador</th>
                @if ($tipo_solicitud != 'reposiciones_global' && $tipo_solicitud != 'reposiciones_detallado')
                    <th>fecha solicitud</th>
                    @if (strtoupper($tipo_solicitud) == 'AUSENCIA')
                        <th>fecha solicitada</th>
                        <th>tiempo total</th>
                        <th>tipo</th>
                        <th>recuperable</th>
                    @endif
                    <th>estado</th>
                    <th>detalle</th>
                @elseif($tipo_solicitud === 'reposiciones_global')
                    <th>Tiempo solicitado</th>
                    <th>Tiempo recuperado</th>
                @else
                    <th>fecha</th>
                    <th>hora desde</th>
                    <th>hora fin</th>
                    <th>tiempo total</th>
                    <th>estado</th>
                    <th>motivo</th>
                @endif
            </tr>
            <tbody>
                @forelse ($query as $items)
                    @if ($tipo_solicitud == 'reposiciones_detallado')
                        @foreach ($items->reposiciones as $reposiciones)
                            <tr>
                                <td>{{ $reposiciones->usuario->nombre }}</td>
                                <td>{{ $reposiciones->fecha }}</td>
                                <td>{{ $reposiciones->hora_desde }}</td>
                                <td>{{ $reposiciones->hora_hasta }}</td>
                                <td>{{ $reposiciones->total }}</td>
                                <td>{{ $reposiciones->estado->descripcion }}</td>
                                <td>{{ $reposiciones->detalle }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                {{ $items->usuario->nombre }}
                            </td>
                            @if ($tipo_solicitud != 'reposiciones_global' && $tipo_solicitud != 'reposiciones_detallado')
                                <td>
                                    {{ $items->fecha_solicitud }}
                                </td>
                                @if (strtoupper($tipo_solicitud) == 'AUSENCIA')
                                    <td>
                                        {{ $items->fecha_desde }} hasta {{ $items->fecha_hasta }}
                                    </td>
                                    <td>
                                        {{ $items->total_tiempo }}
                                    </td>
                                    <td>
                                        {{ $items->tipo_solicitud->descripcion }}
                                    </td>

                                    <td class="text-center">
                                        {{ $items->recuperable ? 'SI' : 'NO' }}
                                    </td>
                                @endif
                                <td>
                                    {{ $items->estado_solicitud->descripcion }}
                                </td>
                                <td>
                                    {{ $items->detalle }}
                                </td>
                            @elseif($tipo_solicitud == 'reposiciones_global')
                                <td>{{ $items->tiempo_acumulado_formateado }}</td>
                                <td>{{ $items->tiempo_recuperado_formateado }}</td>
                            @endif
                        </tr>
                    @endif
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
