<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Global de Adquisiciones</title>
    <style>
        /* CAMBIO: Se ajustan los márgenes de la página para dar espacio al pie de página */
        @page {
            margin: 20px 40px 50px 40px;
            /* top, right, bottom, left */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
            text-transform: uppercase;
        }

        /* CAMBIO: La cabecera ya no es fija. Es una tabla normal. */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        /* CAMBIO: El footer SÍ es fijo para que se repita en cada página */
        .footer {
            width: 100%;
            position: fixed;
            bottom: -30px;
            /* Ajusta esta posición si es necesario */
            left: 0;
            right: 0;
            text-align: right;
            font-size: 8px;
        }

        .pagenum:before {
            content: counter(page);
        }

        h2 {
            font-size: 16px;
            margin: 0;
        }

        h5 {
            font-size: 12px;
            margin: 10px 0 5px 0;
        }

        h6 {
            font-size: 10px;
            margin: 5px 0;
        }

        .titulo_etapa {
            background-color: #e9ecef;
            padding: 5px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 10px;
        }

        .tipo_adquisicion {
            color: #17a2b8;
            font-weight: bold;
            margin-bottom: 2px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        /* Asegura que la cabecera de la tabla se repita si la tabla se corta */
        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            z-index: -1000;
            opacity: 0.1;
            font-size: 100px;
            font-weight: bold;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="watermark">{{ $empresa->nombre_comercial ?? 'SICPRO' }}</div>

    <footer class="footer">Página <span class="pagenum"></span></footer>

    <table class="header-table">
        <tr>
            <td style="width: 25%;"><img src="{{ logoBase64() }}" alt="Logo" width="120"></td>
            <td style="width: 50%; text-align: center;">
                <h2>Reporte Global de Adquisiciones</h2>
                <h6 style="margin:0;">Fecha de Reporte: {{ date('Y-m-d') }}</h6>
            </td>
            <td style="width: 25%;"></td>
        </tr>
    </table>
    <hr>

    <main>
        <h5 style="color:#007bff;">Proyecto: {{ $data['proyecto'] }}</h5>
        @if ($data['subproyecto'])
            <h6 style="color:#007bff;">SubProyecto: {{ $data['subproyecto'] }}</h6>
        @endif

        @php $totalGeneralProyecto = 0; @endphp

        @foreach ($data['data'] as $etapaNombre => $categorias)
            @php $totalEtapa = 0; @endphp
            <div class="titulo_etapa">Etapa: {{ $etapaNombre }}</div>

            {{-- Contratistas --}}
            @if (!empty($categorias['contratista']))
                @php $totalContratistas = $totalPagos = $totalSaldos = 0; @endphp
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
                        @foreach ($categorias['contratista'] as $c)
                            <tr>
                                <td>{{ $c['proveedor'] }}</td>
                                <td>{{ $c['categoria'] }}</td>
                                {{-- CAMBIO: Corregido a 4 decimales --}}
                                <td class="text-center">{{ number_format($c['cantidad'], 4) }}</td>
                                <td class="text-right">${{ number_format($c['total_contratado'], 4) }}</td>
                                <td class="text-right">${{ number_format($c['pagos'], 4) }}</td>
                                <td class="text-right">${{ number_format($c['saldo'], 4) }}</td>
                            </tr>
                            @php
                                $totalContratistas += $c['total_contratado'];
                                $totalPagos += $c['pagos'];
                                $totalSaldos += $c['saldo'];
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="total-row">
                        <tr>
                            <td colspan="3" class="text-right"><strong>Totales Contratista:</strong></td>
                            {{-- CAMBIO: Corregido a 4 decimales --}}
                            <td class="text-right"><strong>${{ number_format($totalContratistas, 4) }}</strong></td>
                            <td class="text-right"><strong>${{ number_format($totalPagos, 4) }}</strong></td>
                            <td class="text-right"><strong>${{ number_format($totalSaldos, 4) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                @php $totalEtapa += $totalContratistas; @endphp
            @endif

            {{-- Mano de Obra --}}
            @if (!empty($categorias['mano_obra']))
                @php $totalManoObra = 0; @endphp
                <h6 class="tipo_adquisicion">Mano de Obra</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Semanas/Periodos</th>
                            <th>Total Pagado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorias['mano_obra'] as $m)
                            {{-- CAMBIO: Corregido a 4 decimales (aunque 'cantidad' podría ser entero, lo mantenemos por consistencia) --}}
                            <tr>
                                <td>{{ number_format($m['cantidad'], 4) }}</td>
                                <td class="text-right">${{ number_format($m['total'], 4) }}</td>
                            </tr>
                            @php $totalManoObra += $m['total']; @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="total-row">
                        <tr>
                            <td class="text-right"><strong>Total Mano de Obra:</strong></td>
                            <td class="text-right"><strong>${{ number_format($totalManoObra, 4) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                @php $totalEtapa += $totalManoObra; @endphp
            @endif

            {{-- Materiales y Herramientas --}}
            @if (!empty($categorias['materiales_herramientas']))
                @php $totalMateriales = 0; @endphp
                <h6 class="tipo_adquisicion">Materiales y Herramientas</h6>
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
                        @foreach ($categorias['materiales_herramientas'] as $mat)
                            <tr>
                                <td>{{ $mat['articulo'] }}</td>
                                <td>{{ $mat['unidad_medida'] }}</td>
                                {{-- CAMBIO: Corregido a 4 decimales --}}
                                <td class="text-center">{{ number_format($mat['cantidad_total'], 4) }}</td>
                                <td class="text-right">${{ number_format($mat['total'], 4) }}</td>
                            </tr>
                            @php $totalMateriales += $mat['total']; @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="total-row">
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total Materiales:</strong></td>
                            <td class="text-right"><strong>${{ number_format($totalMateriales, 4) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                @php $totalEtapa += $totalMateriales; @endphp
            @endif

            {{-- Servicios --}}
            @if (!empty($categorias['servicios']))
                @php $totalServicios = 0; @endphp
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
                        @foreach ($categorias['servicios'] as $serv)
                            <tr>
                                <td>{{ $serv['articulo'] }}</td>
                                <td>{{ $serv['unidad_medida'] }}</td>
                                {{-- CAMBIO: Corregido a 4 decimales --}}
                                <td class="text-center">{{ number_format($serv['cantidad_total'], 4) }}</td>
                                <td class="text-right">${{ number_format($serv['total'], 4) }}</td>
                            </tr>
                            @php $totalServicios += $serv['total']; @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="total-row">
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total Servicios:</strong></td>
                            <td class="text-right"><strong>${{ number_format($totalServicios, 4) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                @php $totalEtapa += $totalServicios; @endphp
            @endif

            {{-- Total por Etapa --}}
            <table style="border: none; margin-top: -5px;">
                <tr>
                    <td class="text-right" style="border: none; font-size: 11px;">
                        {{-- CAMBIO: Corregido a 4 decimales --}}
                        <strong>Total Etapa {{ $etapaNombre }}: ${{ number_format($totalEtapa, 4) }}</strong>
                    </td>
                </tr>
            </table>

            @php $totalGeneralProyecto += $totalEtapa; @endphp
        @endforeach

        <hr>
        <div style="text-align:right; font-size: 14px; margin-top: 10px;">
            <strong>Total General del proyecto: ${{ number_format($totalGeneralProyecto, 4) }}</strong>
        </div>
        @if ($costos_indirecto > 0)
            <div style="text-align:right; font-size: 14px; margin-top: 10px;">
                <strong>Costos Indirectos: {{ $costos_indirecto }}%</strong>
            </div>
            <div style="text-align:right; font-size: 14px; margin-top: 10px;">
                <strong>Total general:
                    ${{ number_format($total_general, 4) }}</strong>
            </div>
        @endif
    </main>
</body>

</html>
