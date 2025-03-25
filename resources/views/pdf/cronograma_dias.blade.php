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
                    <th scope="col">Rubro</th>

                    @foreach ($info_cronograma_dias['diasSemana'] as $dia)
                        <th>{{ ucfirst($dia) }}</th>
                    @endforeach
                    <th scope="col">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($info_cronograma_dias['tablaDatos'] as $rubro => $dias)
                    <tr>
                        <td>{{ $rubro }}</td>
                        @foreach ($info_cronograma_dias['diasSemana'] as $dia)
                            <td class="{{ $dias[$dia]['id'] ? 'bg-success' : '' }}"></td>
                            {!! Form::hidden("dias[$dia]['id']", $dias[$dia]['id'] ?? 'null') !!}
                        @endforeach
                        <td class="aling-middle">
                            {{ implode(', ', array_column($info_cronograma_dias['tablaDatos'], 'observacion')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


    </div>
</body>

</html>
