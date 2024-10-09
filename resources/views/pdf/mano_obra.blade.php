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

        .page-title {
            text-align: center;
            font-size: 24px; /* Tamaño del título */
            font-weight: bold; /* Grosor del título */
            margin: 20px 0; /* Margen superior e inferior */
        }
        .title{
            font-size: 16px; /* Tamaño del título */
            font-weight: bold; /* Grosor del título */
        }
        .text-content{
            font-size: 16px;
        }

        /* Estilos para la marca de agua */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.2; /* Transparencia */
            
            background-position: center;
            background-size: contain;
            background-repeat: no-repeat;
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

        .clear {
            clear: both;
        }
        /* CSS Table */
        .items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .items th,
        .items td {
            padding: 5px;
            border: 1px solid #ddd;
            text-align: center;
            background-color: #ffffff;
        }

        .items th {
            background-color: #f2f2f2;
        }

        /* Definir ancho fijo para las nuevas columnas */
        th:nth-child(11), td:nth-child(11), /* Detalle Adicional */
        th:nth-child(14), td:nth-child(14), /* Detalle Descuento */
        th:nth-child(16), td:nth-child(16)   {
            width: 100px; /* Ajustar el ancho según sea necesario */
            word-wrap: break-word; /* Ajustar contenido largo */
        }

        th:nth-child(1), td:nth-child(1), /* Detalle Adicional */
        th:nth-child(12), td:nth-child(12)  /* Detalle Descuento */ {
            width: 20px; /* Ajustar el ancho según sea necesario */
        }

        /* Definir un ancho mínimo para otras columnas */
        th, td {
            min-width: 50px; /* Asegura que las columnas no se encojan demasiado */
        }

        /* Asegura que las columnas principales mantengan su tamaño adecuado */
       
        th:nth-child(2), td:nth-child(2), /* Nombres y Apellidos */
        th:nth-child(3), td:nth-child(3), /* Cargo */
        th:nth-child(10), td:nth-child(10), /* Adicionales */
        th:nth-child(12), td:nth-child(12), /* Descuentos */
        th:nth-child(13), td:nth-child(13), /* TOTAL */
        th:nth-child(15), td:nth-child(15), /* Liquido a Recibir */
        th:nth-child(16), td:nth-child(16),
        th:nth-child(17), td:nth-child(17)   {
            min-width: 80px;
        }
        /* end */
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>
    <!-- Marca de agua -->
    <div class="watermark">
        <img src="data:image/png;base64,{{ $logo_base64 }}"/>
    </div>

    <div class="content">
        <h1 class="page-title">{{ $info_mano_obra['proyecto'] }}</h1>
        <div class="details">
            <div class="left">
                <strong class="title">Fecha:</strong> <span class="text-content">{{ $info_mano_obra['fecha'] }}</span><br>
                <strong class="title">Semana:</strong> <span class="text-content"> {{ $info_mano_obra['semana'] }}</span><br>
                <strong class="title">Etapa:</strong> <span class="text-content">{{ $info_mano_obra['etapa'] }}</span>
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
                        <th>S</th>
                        <th>Adicionales</th>
                        <th>Detalle Adicionales</th>
                        <th>TOTAL</th>
                        <th>Descuento</th>
                        <th>Detalle Descuento</th>
                        <th>Liquido a Recibir</th>
                        <th>Observaciones</th>
                        <th>Firma</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lastName = '';
                        $rowspan = 1;
                        $index = 1;
                        $totalDias = 0;
                        $liquidoRecibirTotal = 0;

                        $totalAdicionales = 0;
                        $totalPagoDias = 0;
                        $totalDescuentos = 0;
                        $totalRecibir = 0;
                    @endphp
        
                    @foreach ($info_mano_obra['detalle'] as $detalle)
                        @php
                            // Verificar si el nombre es diferente al anterior
                            $isFirstRowForName = ($detalle['nombre'] !== $lastName);

                            if ($isFirstRowForName) {
                                // Contar cuántas filas pertenecen al mismo nombre
                                $rowspan = collect($info_mano_obra['detalle'])
                                    ->where('nombre', $detalle['nombre'])
                                    ->unique('cargo') // Asegura que se cuenten solo cargos distintos
                                    ->count();
                                
                                // Calcular el total de días trabajados + adicionales para todas las filas de este trabajador
                                $totalDias = collect($info_mano_obra['detalle'])
                                    ->where('nombre', $detalle['nombre'])
                                    ->sum(function($d) {
                                        return array_sum($d['dias']) + $d['total_adicional'];
                                    });
                                
                                // Calcular el total del líquido a recibir (total - descuento)
                                $liquidoRecibirTotal = collect($info_mano_obra['detalle'])
                                    ->where('nombre', $detalle['nombre'])
                                    ->sum(function($d) {
                                        return ($d['total'] - $d['total_descuento']);
                                    });
                            }

                            // Sumar los valores para los totales generales
                            $totalAdicionales += $detalle['total_adicional'];
                            $totalPagoDias += array_sum($detalle['dias']) + $detalle['total_adicional'];
                            $totalDescuentos += $detalle['total_descuento'];
                            $totalRecibir += ($detalle['total'] - $detalle['total_descuento']);
                        @endphp
                        <tr>
                            @if($isFirstRowForName)
                                <td rowspan="{{ $rowspan }}">{{ $index}}</td>
                                <td rowspan="{{ $rowspan }}">{{ $detalle['nombre'] }}</td>
                                @php $lastName = $detalle['nombre']; $index ++; @endphp
                            @endif
        
                            <td>{{ $detalle['cargo']}}</td>
                            @foreach($detalle['dias'] as $dia)
                                <td>$ {{ number_format($dia, 2) }}</td>
                            @endforeach
                            <td>$ {{ number_format($detalle['total_adicional'], 2) }}</td>
                            <td>
                                {{ implode(',', $detalle['detalle_adicional']) }}
                            </td>
                            @if($isFirstRowForName)
                                <td rowspan="{{ $rowspan }}">$ {{ number_format($totalDias, 2) }}</td>
                            @endif
                            <td>$ {{ number_format($detalle['total_descuento'], 2) }}</td>
                            <td>
                                {{ implode(',', $detalle['detalle_descuento']) }}
                            </td>
                            @if($isFirstRowForName)
                                <td rowspan="{{ $rowspan }}">$ {{ number_format($liquidoRecibirTotal, 2) }}</td>
                            @endif
                            <td>
                                {{ implode(',', $detalle['observacion']) }}
                            </td>
                            <td></td>
                        </tr>
                    @endforeach
                    <!-- Fila de totales generales -->
                    <tr>
                        <td colspan="9"><strong>Total:</strong></td>
                        <td><strong>$ {{ number_format($totalAdicionales, 2) }}</strong></td>
                        <td></td> <!-- Detalle adicional -->
                        <td><strong>$ {{ number_format($totalPagoDias, 2) }}</strong></td>
                        <td><strong>$ {{ number_format($totalDescuentos, 2) }}</strong></td>
                        <td></td> <!-- Detalle descuento -->
                        <td><strong>$ {{ number_format($totalRecibir, 2) }}</strong></td>
                        <td></td>
                        <td></td> <!-- Firma -->
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Esta es un informe generado electrónicamente. No requiere firma.
    </div>
</body>

</html>
