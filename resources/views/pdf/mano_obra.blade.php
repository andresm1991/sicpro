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
        }

        .items th {
            background-color: #f2f2f2;
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
    
    <div class="content">
        <h1 class="page-title">{{ $info_mano_obra['proyecto'] }}</h1>
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
                        <th>S</th>
                        <th>Adicionales</th>
                        <th>TOTAL</th>
                        <th>Descuento</th>
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
                    @endphp
        
                    @foreach ($info_mano_obra['detalle'] as $detalle)
                        @php
                            // Verificar si el nombre es diferente al anterior
                            $isFirstRowForName = ($detalle['nombre'] !== $lastName);

                            if ($isFirstRowForName) {
                                // Contar cuántas filas pertenecen al mismo nombre
                                $rowspan = collect($info_mano_obra['detalle'])->where('nombre', $detalle['nombre'])->count();
                                
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
                            @if($isFirstRowForName)
                                <td rowspan="{{ $rowspan }}">$ {{ number_format($totalDias, 2) }}</td>
                            @endif
                            <td>$ {{ number_format($detalle['total_descuento'], 2) }}</td>
                            @if($isFirstRowForName)
                                <td rowspan="{{ $rowspan }}">$ {{ number_format($liquidoRecibirTotal, 2) }}</td>
                            @endif
                            <td>{{ $detalle['observacion'] }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Esta es un informe generado electrónicamente. No requiere firma.
    </div>
</body>

</html>
