{{-- filepath: c:\laragon\www\sicpro\resources\views\pdf\reporte_adquisiciones_global.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Global de Adquisiciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            text-transform: uppercase;
        }

        hr {
            background-color: #bebdbe;
            height: 0px;
            border: transparent;
        }

        .titulo_etapa {
            color: #6c757d;
            font-size: 14px;
            font-weight: bold;
        }

        .tipo_adquisicion {
            color: #17a2b8;
            font-size: 10;
            font-weight: bold;
            margin-bottom: 5px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 2px;
        }

        th {
            background: #eee;
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



        h5,
        h6 {
            margin-bottom: 0;
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
    </style>
</head>

<body>
    <!-- Marca de agua -->
    <div class="watermark">
        <img src="{{ logoBase64() }}" />
    </div>
    <table class="table-header">
        <tbody>
            <tr>
                <td style="width: 1px;">
                    <img src="{{ logoBase64() }}" alt="Logo del Proyecto" width="150">
                </td>
                <td class="text-center">
                    <h2>Reporte global de adquisiciones</h2>
                </td>
            </tr>

        </tbody>
    </table>
    <h6>Fecha Reporte: {{ date('Y-m-d') }}</h6>
    @php $totalGeneral = 0; @endphp

    @foreach ($data['data'] as $proyecto => $etapas)
        <h5 style="color:#007bff;">Proyecto: {{ $proyecto }}</h5>
        @if ($data['subproyecto'])
            <h5 style="color:#007bff;">SubProyecto: {{ $data['subproyecto'] }}</h5>
        @endif
        <hr>
        @foreach ($etapas as $etapa => $info)
            <h6 class="titulo_etapa">Etapa: {{ $etapa }}</h6>

            {{-- Materiales y Herramientas --}}
            @php $totalMateriales = 0; @endphp
            @if (!empty($info['materiales_herramientas']))
                <h6 class="tipo_adquisicion">Materiales y Herramientas</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Unidad de Medida</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($info['materiales_herramientas'] as $mat)
                            <tr>
                                <td>{{ $mat['articulo'] }}</td>
                                <td>{{ $mat['unidad_medida'] }}</td>
                                <td>{{ number_format($mat['cantidad_total'], 4) }}</td>
                                <td>${{ number_format($mat['total'], 4) }}</td>
                            </tr>
                            @php $totalMateriales += $mat['total']; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;"><strong>Total Materiales y
                                    Herramientas:</strong>
                            </td>
                            <td style="text-align:right;"><strong>${{ number_format($totalMateriales, 4) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif

            {{-- Servicios --}}
            @php $totalServicios = 0; @endphp
            @if (!empty($info['servicios']))
                <h6 class="tipo_adquisicion">Servicios</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($info['servicios'] as $serv)
                            <tr>
                                <td>{{ $serv['articulo'] }}</td>
                                <td>{{ $serv['unidad_medida'] }}</td>
                                <td>{{ number_format($serv['cantidad_total'], 4) }}</td>
                                <td>${{ number_format($serv['total'], 4) }}</td>
                            </tr>
                            @php $totalServicios += $serv['total']; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;"><strong>Total Servicios:</strong></td>
                            <td style="text-align:right;"><strong>${{ number_format($totalServicios, 4) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif

            {{-- Contratistas --}}
            @php
                $totalContratistas = 0;
                $totalPagos = 0;
                $totalSaldos = 0;
            @endphp
            @if (!empty($info['contratista']))
                <h6 class="tipo_adquisicion">Contratistas</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Total Contratado</th>
                            <th>Pagos</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($info['contratista'] as $c)
                            <tr>
                                <td>{{ $c['proveedor'] }}</td>
                                <td>{{ $c['categoria'] }}</td>
                                <td>{{ number_format($c['cantidad'], 4) }}</td>
                                <td style="text-align:right;">${{ number_format($c['total_contratado'], 4) }}</td>
                                <td style="text-align:right;">${{ number_format($c['pagos'], 4) }}</td>
                                <td style="text-align:right;">${{ number_format($c['saldo'], 4) }}</td>
                            </tr>
                            @php
                                $totalContratistas += $c['total_contratado'];
                                $totalPagos += $c['pagos'];
                                $totalSaldos += $c['saldo'];
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;"><strong>Totales:</strong></td>
                            <td style="text-align:right;"><strong>${{ number_format($totalContratistas, 4) }}</strong>
                            </td>
                            <td style="text-align:right;"><strong>${{ number_format($totalPagos, 4) }}</strong></td>
                            <td style="text-align:right;"><strong>${{ number_format($totalSaldos, 4) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            @endif

            {{-- Mano de Obra --}}
            @php $totalManoObra = 0; @endphp
            @if (!empty($info['mano_obra']))
                <h6 class="tipo_adquisicion">Mano de obra</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Semanas</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($info['mano_obra'] as $m)
                            <tr>
                                <td>{{ number_format($m['cantidad'], 4) }}</td>
                                <td style="text-align:right;">${{ number_format($m['total'], 4) }}</td>
                            </tr>
                            @php $totalManoObra += $m['total']; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="text-align:right;"><strong>Total Mano de Obra:</strong></td>
                            <td style="text-align:right;"><strong>${{ number_format($totalManoObra, 4) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            @endif

            @php $totalGeneral += $totalMateriales + $totalServicios + $totalPagos + $totalManoObra; @endphp
            <br>
        @endforeach
    @endforeach

    <div style="text-align:right; margin-top:20px;">
        <strong>Total General: ${{ number_format($totalGeneral, 4) }}</strong>
    </div>
</body>

</html>
