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

        ul {

            padding: 0 10px 0 10px;
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
        <table class="table-header">
            <tbody>
                <tr>
                    <td style="width: 1px;">
                        <img src="data:image/png;base64,{{ logoBase64() }}" alt="Logo del Proyecto" width="150">
                    </td>
                    <td class="text-center">
                        <h2>Cronograma de actividades por días</h2>
                        <h2>{{ $info_cronograma_dias['proyecto'] }}</h2>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="details">
                            <span style="font-weight: bold">Semana:
                            </span><span>{{ $info_cronograma_dias['semana'] }}</span><br>
                            <span style="font-weight: bold">Fecha:
                            </span><span>{{ dateFormatHumans($info_cronograma_dias['fecha_semana']) }}</span><br>

                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Tabla -->
        <table class="tabla-actividades">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Día</th>
                    <th scope="col">Rubro</th>
                    <th scope="col">Observación</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rubroActual = null; // Rastrea el rubro actual
                    $rubrosPorDia = $info_cronograma_dias['rubrosPorDia'];
                    $rubrosAgrupados = $info_cronograma_dias['rubrosAgrupados'];
                    $diasSemana = $info_cronograma_dias['diasSemana'];
                @endphp

                @foreach ($diasSemana as $dia)
                    @php

                        $rubroDia = $rubrosPorDia[$dia] ?? null;
                        $esInicioRubro = false;

                        // Detectar el inicio de un nuevo rubro
                        if ($rubroDia && $rubroDia['id'] !== ($rubroActual['id'] ?? null)) {
                            $rubroActual = $rubroDia;
                            $esInicioRubro = true;
                        }

                        // Contar cuántos días abarca este rubro para el rowspan
                        $rowspan = 1;
                        if ($esInicioRubro) {
                            $rowspan = count($rubrosAgrupados[$rubroDia['id']]['dias']);
                        }

                        // Obtener la observación del día (si existe)
                        $observacion = $rubroDia['observacion'] ?? '';
                    @endphp

                    <tr>
                        <!-- Nombre del día con checkbox -->
                        <td class="align-middle font-weight-bold">
                            <div class="form-check">
                                <input name="dias[{{ $dia }}][checked]" class="form-check-input"
                                    type="checkbox" value="{{ $rubroDia['id'] ?? '' }}" id="check_{{ $dia }}"
                                    {{ isset($rubroDia) ? 'checked' : '' }} data-rubro-id="{{ $rubroDia['id'] ?? '' }}">
                                <label class="form-check-label" for="check_{{ $dia }}">
                                    {{ ucfirst($dia) }}
                                </label>
                            </div>
                        </td>

                        <!-- Rubro (solo en la primera fila del grupo) -->
                        @if ($esInicioRubro)
                            <td rowspan="{{ $rowspan }}" class="align-middle text-center font-weight-bold">
                                {{ $rubroDia['nombre'] }}
                            </td>
                        @elseif(!$rubroDia)
                            <td class="align-middle text-center font-weight-bold"></td>
                        @endif

                        <!-- Observación -->
                        <td class="align-middle">
                            {!! $observacion !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>


    </div>
</body>

</html>
