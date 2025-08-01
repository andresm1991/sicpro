<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Comparativo de Adquisiciones</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 9px;
            color: #333;
            text-transform: uppercase;
        }

        @page {
            margin: 25px 35px;
        }

        header {
            margin-bottom: 20px;
        }

        .report-title {
            text-align: center;
            font-size: 16px;
            margin-bottom: 5px;
            color: #0056b3;
        }

        .report-subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 20px;
        }

        /* --- INICIO DE CORRECCIONES CSS --- */

        /* CORRECCIÓN 1: Se elimina la clase '.etapa-container' que ya no es necesaria. */

        /* CORRECCIÓN 2: Se añade la regla clave al título de la etapa.
           Esto evita que el título quede solo al final de una página, sin forzar
           que toda la etapa se mueva a la página siguiente. */
        .etapa-header {
            background-color: #e9ecef;
            padding: 6px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            border-radius: 4px;
            page-break-after: avoid;
            /* <-- LA SOLUCIÓN MÁGICA */
        }

        .page-break {
            page-break-before: always;
        }

        /* --- FIN DE CORRECCIONES CSS --- */

        .category-title {
            font-size: 11px;
            font-weight: bold;
            color: #17a2b8;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 4px;
            text-align: left;
            vertical-align: middle;
        }

        thead th {
            background-color: #343a40;
            color: #fff;
            text-align: center;
            font-weight: bold;
            font-size: 8.5px;
        }

        tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        tfoot {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-muted {
            color: #6c757d;
        }
    </style>
</head>

<body>
    @php
        // CORRECCIÓN: Se inicializan los totales generales para todo el reporte, tal como en el jQuery.
        $totalesGenerales = [
            'p1' => ['contratado' => 0, 'materiales' => 0, 'servicios' => 0, 'mano_obra' => 0],
            'p2' => ['contratado' => 0, 'materiales' => 0, 'servicios' => 0, 'mano_obra' => 0],
        ];
    @endphp

    <header>
        <h1 class="report-title">Reporte Comparativo de Adquisiciones</h1>
        <div class="report-subtitle">
            <strong>Proyecto 1:</strong> {{ $data['proyecto1']['nombre'] }} vs <strong>Proyecto 2:</strong>
            {{ $data['proyecto2']['nombre'] }}
            @if (!empty($data['subproyecto']))
                <br><strong>Subproyecto/Filtro:</strong> {{ $data['subproyecto'] }}
            @endif
        </div>
    </header>

    <main>
        {{-- Bucle principal por cada etapa, replicando la lógica de jQuery --}}
        @foreach ($data['data_comparativa'] as $etapaNombre => $categorias)
            @if (!$loop->first)
                <div class="page-break"></div>
            @endif
            @php
                // Se inicializan los totales para esta etapa específica en cada iteración.
                $totalesEtapa = ['p1' => 0, 'p2' => 0];
            @endphp
            <div class="etapa-header">Etapa: {{ $etapaNombre }}</div>

            {{-- SECCIÓN CONTRATISTAS --}}
            @if (!empty($categorias['contratista']))
                @php
                    $subtotales = [
                        'p1' => ['total' => 0, 'pagos' => 0, 'saldo' => 0],
                        'p2' => ['total' => 0, 'pagos' => 0, 'saldo' => 0],
                    ];
                @endphp
                <div class="table-wrapper">
                    <div class="category-title">Contratistas</div>
                    <table>
                        <thead>
                            <tr>
                                <th rowspan="2" width="22%">Proveedor / Categoría</th>
                                <th colspan="3">{{ $data['proyecto1']['nombre'] }}</th>
                                <th colspan="3">{{ $data['proyecto2']['nombre'] }}</th>
                                <th colspan="3">Diferencia</th>
                            </tr>
                            <tr>
                                <th class="text-right">Contratado</th>
                                <th class="text-right">Pagos</th>
                                <th class="text-right">Saldo</th>
                                <th class="text-right">Contratado</th>
                                <th class="text-right">Pagos</th>
                                <th class="text-right">Saldo</th>
                                <th class="text-right">Contratado</th>
                                <th class="text-right">Pagos</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categorias['contratista'] as $item)
                                @php
                                    $subtotales['p1']['total'] += $item['p1']['total_contratado'] ?? 0;
                                    $subtotales['p1']['pagos'] += $item['p1']['pagos'] ?? 0;
                                    $subtotales['p1']['saldo'] += $item['p1']['saldo'] ?? 0;
                                    $subtotales['p2']['total'] += $item['p2']['total_contratado'] ?? 0;
                                    $subtotales['p2']['pagos'] += $item['p2']['pagos'] ?? 0;
                                    $subtotales['p2']['saldo'] += $item['p2']['saldo'] ?? 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $item['item_base']['proveedor'] }}</strong><br><small
                                            class="text-muted">{{ $item['item_base']['categoria'] }}</small></td>
                                    <td class="text-right">
                                        {{ number_format($item['p1']['total_contratado'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-right">{{ number_format($item['p1']['pagos'] ?? 0, 2) }}</td>
                                    <td class="text-right">{{ number_format($item['p1']['saldo'] ?? 0, 2) }}</td>
                                    <td class="text-right">
                                        {{ number_format($item['p2']['total_contratado'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-right">{{ number_format($item['p2']['pagos'] ?? 0, 2) }}</td>
                                    <td class="text-right">{{ number_format($item['p2']['saldo'] ?? 0, 2) }}</td>
                                    <td
                                        class="text-right {{ ($item['diff']['total'] ?? 0) > 0 ? 'text-success' : (($item['diff']['total'] ?? 0) < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($item['diff']['total'] ?? 0, 2) }}</td>
                                    <td
                                        class="text-right {{ ($item['diff']['pagos'] ?? 0) > 0 ? 'text-success' : (($item['diff']['pagos'] ?? 0) < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($item['diff']['pagos'] ?? 0, 2) }}</td>
                                    <td
                                        class="text-right {{ ($item['diff']['saldo'] ?? 0) > 0 ? 'text-success' : (($item['diff']['saldo'] ?? 0) < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($item['diff']['saldo'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Totales Contratista</strong></td>
                                <td class="text-right">{{ number_format($subtotales['p1']['total'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p1']['pagos'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p1']['saldo'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p2']['total'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p2']['pagos'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p2']['saldo'], 2) }}</td>
                                <td
                                    class="text-right {{ $subtotales['p1']['total'] - $subtotales['p2']['total'] > 0 ? 'text-success' : ($subtotales['p1']['total'] - $subtotales['p2']['total'] < 0 ? 'text-danger' : '') }}">
                                    {{ number_format($subtotales['p1']['total'] - $subtotales['p2']['total'], 2) }}
                                </td>
                                <td
                                    class="text-right {{ $subtotales['p1']['pagos'] - $subtotales['p2']['pagos'] > 0 ? 'text-success' : ($subtotales['p1']['pagos'] - $subtotales['p2']['pagos'] < 0 ? 'text-danger' : '') }}">
                                    {{ number_format($subtotales['p1']['pagos'] - $subtotales['p2']['pagos'], 2) }}
                                </td>
                                <td
                                    class="text-right {{ $subtotales['p1']['saldo'] - $subtotales['p2']['saldo'] > 0 ? 'text-success' : ($subtotales['p1']['saldo'] - $subtotales['p2']['saldo'] < 0 ? 'text-danger' : '') }}">
                                    {{ number_format($subtotales['p1']['saldo'] - $subtotales['p2']['saldo'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @php
                    $totalesEtapa['p1'] += $subtotales['p1']['total'];
                    $totalesEtapa['p2'] += $subtotales['p2']['total'];
                    $totalesGenerales['p1']['contratado'] += $subtotales['p1']['total'];
                    $totalesGenerales['p2']['contratado'] += $subtotales['p2']['total'];
                @endphp
            @endif

            {{-- INICIO: SECCIÓN MATERIALES Y HERRAMIENTAS (lógica integrada aquí) --}}
            @if (!empty($categorias['materiales_herramientas']))
                @php
                    $subtotales = [
                        'p1' => ['cantidad' => 0, 'total' => 0],
                        'p2' => ['cantidad' => 0, 'total' => 0],
                    ];
                @endphp
                <div class="table-wrapper">
                    <div class="category-title">Materiales y Herramientas</div>
                    <table>
                        <thead>
                            <tr>
                                <th rowspan="2" width="25%">Item / Unidad</th>
                                <th colspan="2" class="text-center">{{ $data['proyecto1']['nombre'] }}</th>
                                <th colspan="2" class="text-center">{{ $data['proyecto2']['nombre'] }}</th>
                                <th colspan="2" class="text-center">Diferencia</th>
                            </tr>
                            <tr>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categorias['materiales_herramientas'] as $item)
                                @php
                                    $subtotales['p1']['cantidad'] += $item['p1']['cantidad_total'] ?? 0;
                                    $subtotales['p1']['total'] += $item['p1']['total'] ?? 0;
                                    $subtotales['p2']['cantidad'] += $item['p2']['cantidad_total'] ?? 0;
                                    $subtotales['p2']['total'] += $item['p2']['total'] ?? 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $item['item_base']['articulo'] }}</strong><br><small
                                            class="text-muted">{{ $item['item_base']['unidad_medida'] }}</small>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($item['p1']['cantidad_total'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-right">{{ number_format($item['p1']['total'] ?? 0, 2) }}</td>
                                    <td class="text-right">
                                        {{ number_format($item['p2']['cantidad_total'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-right">{{ number_format($item['p2']['total'] ?? 0, 2) }}</td>
                                    <td
                                        class="text-right {{ ($item['diff']['cantidad'] ?? 0) > 0 ? 'text-success' : (($item['diff']['cantidad'] ?? 0) < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($item['diff']['cantidad'] ?? 0, 2) }}</td>
                                    <td
                                        class="text-right {{ ($item['diff']['total'] ?? 0) > 0 ? 'text-success' : (($item['diff']['total'] ?? 0) < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($item['diff']['total'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Totales Materiales y Herramientas</strong></td>
                                <td class="text-right">{{ number_format($subtotales['p1']['cantidad'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p1']['total'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p2']['cantidad'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p2']['total'], 2) }}</td>
                                <td class="text-right">
                                    {{ number_format($subtotales['p1']['cantidad'] - $subtotales['p2']['cantidad'], 2) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($subtotales['p1']['total'] - $subtotales['p2']['total'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @php
                    $totalesEtapa['p1'] += $subtotales['p1']['total'];
                    $totalesEtapa['p2'] += $subtotales['p2']['total'];
                    $totalesGenerales['p1']['materiales'] += $subtotales['p1']['total'];
                    $totalesGenerales['p2']['materiales'] += $subtotales['p2']['total'];
                @endphp
            @endif
            {{-- FIN: SECCIÓN MATERIALES Y HERRAMIENTAS --}}

            {{-- INICIO: SECCIÓN SERVICIOS (lógica integrada aquí) --}}
            @if (!empty($categorias['servicios']))
                @php
                    $subtotales = [
                        'p1' => ['cantidad' => 0, 'total' => 0],
                        'p2' => ['cantidad' => 0, 'total' => 0],
                    ];
                @endphp
                <div class="table-wrapper">
                    <div class="category-title">Servicios</div>
                    <table>
                        <thead>
                            <tr>
                                <th rowspan="2" width="25%">Item / Unidad</th>
                                <th colspan="2" class="text-center">{{ $data['proyecto1']['nombre'] }}</th>
                                <th colspan="2" class="text-center">{{ $data['proyecto2']['nombre'] }}</th>
                                <th colspan="2" class="text-center">Diferencia</th>
                            </tr>
                            <tr>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categorias['servicios'] as $item)
                                @php
                                    $subtotales['p1']['cantidad'] += $item['p1']['cantidad_total'] ?? 0;
                                    $subtotales['p1']['total'] += $item['p1']['total'] ?? 0;
                                    $subtotales['p2']['cantidad'] += $item['p2']['cantidad_total'] ?? 0;
                                    $subtotales['p2']['total'] += $item['p2']['total'] ?? 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $item['item_base']['articulo'] }}</strong><br><small
                                            class="text-muted">{{ $item['item_base']['unidad_medida'] }}</small>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($item['p1']['cantidad_total'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-right">{{ number_format($item['p1']['total'] ?? 0, 2) }}</td>
                                    <td class="text-right">
                                        {{ number_format($item['p2']['cantidad_total'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-right">{{ number_format($item['p2']['total'] ?? 0, 2) }}</td>
                                    <td
                                        class="text-right {{ ($item['diff']['cantidad'] ?? 0) > 0 ? 'text-success' : (($item['diff']['cantidad'] ?? 0) < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($item['diff']['cantidad'] ?? 0, 2) }}</td>
                                    <td
                                        class="text-right {{ ($item['diff']['total'] ?? 0) > 0 ? 'text-success' : (($item['diff']['total'] ?? 0) < 0 ? 'text-danger' : '') }}">
                                        {{ number_format($item['diff']['total'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Totales Servicios</strong></td>
                                <td class="text-right">{{ number_format($subtotales['p1']['cantidad'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p1']['total'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p2']['cantidad'], 2) }}</td>
                                <td class="text-right">{{ number_format($subtotales['p2']['total'], 2) }}</td>
                                <td class="text-right">
                                    {{ number_format($subtotales['p1']['cantidad'] - $subtotales['p2']['cantidad'], 2) }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($subtotales['p1']['total'] - $subtotales['p2']['total'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @php
                    $totalesEtapa['p1'] += $subtotales['p1']['total'];
                    $totalesEtapa['p2'] += $subtotales['p2']['total'];
                    $totalesGenerales['p1']['servicios'] += $subtotales['p1']['total'];
                    $totalesGenerales['p2']['servicios'] += $subtotales['p2']['total'];
                @endphp
            @endif
            {{-- FIN: SECCIÓN SERVICIOS --}}

            {{-- SECCIÓN MANO DE OBRA --}}
            @if (!empty($categorias['mano_obra']))
                @php
                    $item_mo = $categorias['mano_obra'][0];
                    $total_p1_mo = $item_mo['p1']['total'] ?? 0;
                    $total_p2_mo = $item_mo['p2']['total'] ?? 0;
                    $diff_total_mo = $item_mo['diff']['total'] ?? 0;

                    $totalesEtapa['p1'] += $total_p1_mo;
                    $totalesEtapa['p2'] += $total_p2_mo;
                    $totalesGenerales['p1']['mano_obra'] += $total_p1_mo;
                    $totalesGenerales['p2']['mano_obra'] += $total_p2_mo;
                @endphp
                <div class="table-wrapper">
                    <div class="category-title">Mano de Obra</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th class="text-center">{{ $data['proyecto1']['nombre'] }}</th>
                                <th class="text-center">{{ $data['proyecto2']['nombre'] }}</th>
                                <th class="text-center">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Pagado Mano de Obra</td>
                                <td class="text-right">{{ number_format($total_p1_mo, 2) }}</td>
                                <td class="text-right">{{ number_format($total_p2_mo, 2) }}</td>
                                <td
                                    class="text-right {{ $diff_total_mo > 0 ? 'text-success' : ($diff_total_mo < 0 ? 'text-danger' : '') }}">
                                    {{ number_format($diff_total_mo, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- TOTAL POR ETAPA --}}
            <div style="text-align: right; margin-bottom: 20px;">
                <div style="display: inline-block; padding: 8px; border-radius: 4px; background-color: #f8f9fa;">
                    <strong>Total Etapa:</strong>
                    <span>{{ number_format($totalesEtapa['p1'], 2) }}</span> vs
                    <span>{{ number_format($totalesEtapa['p2'], 2) }}</span>
                    <strong style="margin-left: 15px;">Diff:
                        {{ number_format($totalesEtapa['p1'] - $totalesEtapa['p2'], 2) }}</strong>
                </div>
            </div>
        @endforeach

        {{-- RESUMEN FINAL --}}
        @php
            $totalP1 = array_sum($totalesGenerales['p1']);
            $totalP2 = array_sum($totalesGenerales['p2']);
        @endphp
        <div class="table-wrapper" style="margin-top: 30px;">
            <div class="etapa-header">Resumen General de Costos</div>
            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-right">{{ $data['proyecto1']['nombre'] }}</th>
                        <th class="text-right">{{ $data['proyecto2']['nombre'] }}</th>
                        <th class="text-right">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (['contratado' => 'Contratistas', 'materiales' => 'Materiales y Herramientas', 'servicios' => 'Servicios', 'mano_obra' => 'Mano de Obra'] as $key => $label)
                        @php
                            $diff = $totalesGenerales['p1'][$key] - $totalesGenerales['p2'][$key];
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="text-right">{{ number_format($totalesGenerales['p1'][$key], 2) }}</td>
                            <td class="text-right">{{ number_format($totalesGenerales['p2'][$key], 2) }}</td>
                            <td
                                class="text-right {{ $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : '') }}">
                                {{ number_format($diff, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; font-size: 11px;">
                        <td>Total General</td>
                        <td class="text-right">{{ number_format($totalP1, 2) }}</td>
                        <td class="text-right">{{ number_format($totalP2, 2) }}</td>
                        <td
                            class="text-right {{ $totalP1 - $totalP2 > 0 ? 'text-success' : ($totalP1 - $totalP2 < 0 ? 'text-danger' : '') }}">
                            {{ number_format($totalP1 - $totalP2, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>
</body>

</html>
