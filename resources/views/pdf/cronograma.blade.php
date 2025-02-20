<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cronograma Valorado</title>
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
            max-width: 150px;
            height: auto;
            vertical-align: middle;
            /* Asegura alineación precisa */
        }

        /* Contenido central */
        .header .content {
            text-align: center;
            flex-grow: 1;
            /* Ocupa el espacio restante entre la imagen y los detalles */
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Centra verticalmente el contenido */
            align-items: center;
            /* Centra horizontalmente el contenido */
            margin-left: 20px;
            /* Espacio entre la imagen y el contenido */
        }

        .header .content small {
            display: block;
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        /* Detalles del proyecto */
        .header .details {
            min-width: 300px;
            /* Espacio suficiente para los detalles */
        }

        .header .details small {
            display: block;
            font-size: 12px;
            margin: 5px 0;
        }

        /* Tabla */
        .tabla-content {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tabla-content th,
        .tabla-content td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            background-color: #ffffff;
        }

        .tabla-content th {
            background-color: #d3d4d6;
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

        /* Style tabla totales*/
        .tabla-totales {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .tabla-totales th,
        .tabla-totales td {
            border: 1px solid black;
            padding: 10px;
            background-color: #ffffff;
        }

        .tabla-totales th {
            font-weight: bold;
        }

        .highlight {
            font-weight: bold;
            text-align: right;
        }

        .sub-header {
            font-size: 12px;
        }

        .text-center {
            text-align: center !important;
        }

        /* Estilo para eñ pintado de las actividad */
        .pintado_pendiente {
            background-color: Crimson !important;
            color: white !important;
        }

        .pintado_completado {
            background-color: rgb(55, 201, 74) !important;
            color: white !important;
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
            /* Transparencia */

            background-position: center;
            background-size: contain;
            background-repeat: no-repeat;
        }
    </style>
</head>

<body>
    <!-- Marca de agua -->
    <div class="watermark">
        <img src="data:image/png;base64,{{ logoBase64() }}" />
    </div>
    <div class="container">
        <!-- Header -->
        <table class="table-header">
            <tbody>
                <tr>
                    <td style="width: 1px;">
                        <img src="data:image/png;base64,{{ logoBase64() }}" alt="Logo del Proyecto" width="150">
                    </td>
                    <td class="text-center">
                        <h2>Cronograma Valorado</h2>
                        <h2>{{ $proyecto->nombre_proyecto }}</h2>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="details">
                            <span style="font-weight: bold">Viviendas:
                            </span><span>{{ $proyecto->numero_unidades }} -
                                {{ $proyecto->area_construccion_unidad }}m²</span><br>
                            <span style="font-weight: bold">Inicio:
                            </span><span>{{ dateFormatHumans($proyecto->fecha_inicio) }}</span><br>
                            <span style="font-weight: bold">Plazo de ejecución:
                            </span><span>{{ $plazo_meses }}
                                meses</span><br>
                            <span style="font-weight: bold">Monto de inversión:
                            </span><span>{{ number_format($proyecto->presupuesto_total, 2) }}</span><br>
                            <span style="font-weight: bold">Técnico responsable:
                            </span><span>{{ $proyecto->tecnico_responsable ?? 'No asignado' }}</span><br>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Tabla -->
        <table class="tabla-content">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Rubro</th>
                    @for ($i = 0; $i < $plazo_semanas; $i++)
                        <th scope="col" class="text-center">{{ $i + 1 }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @php
                    $nro = 1;
                @endphp
                @forelse ($cronograma as $index => $rubro)
                    <tr id="{{ $index }}">
                        <td class="aling-middle">{{ $nro }}</td>
                        <td class="aling-middle">{{ $rubro['rubro_cronograma_nombre'] }}</td>
                        @for ($i = 1; $i <= $plazo_semanas; $i++)
                            <td class="aling-middle text-center editar-rubro {{ isset($rubro['semanas'][$i]) ? 'pintado_pendiente' : '' }}"
                                data-dias="{{ isset($rubro['semanas'][$i]) ? implode(', ', $rubro['semanas'][$i]) : '' }}"
                                data-rubro="{{ isset($rubro['semanas'][$i]) ? $rubro['rubro_cronograma_id'] : '' }}"
                                data-semana="{{ isset($rubro['semanas'][$i]) ? $i : '' }}" style="cursor:pointer;">
                                @if (isset($rubro['semanas'][$i]))
                                    {{ $nro }}
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    @php
                        $nro += 1;
                    @endphp
                @empty
                    <tr>
                        <td colspan="{{ $plazo_semanas + 2 }}" class="text-center">No existen datos para
                            mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <br>
        <table class="tabla-totales">
            <tr>
                <th>100 % ESTRUCTURA</th>
                <th>ETAPA DE MAMPOSTERIAS Y ENLUCIDOS</th>
                <th rowspan="2">ETAPA DE ACABADOS</th>
            </tr>
            <tr>
                <td colspan="2" class="sub-header">100% OBRA GRIS</td>
            </tr>
            <tr>
                <td>
                    $ {{ number_format($total_estructural, 2) }}
                </td>
                <td>
                    $ {{ number_format($total_mpel, 2) }}
                </td>
                <td>
                    $ {{ number_format($total_acabados, 2) }}
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-center">
                    $ {{ number_format($total, 2) }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
