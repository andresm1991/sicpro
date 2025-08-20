@if (!empty($items))
    @php
        $subtotales = ['p1' => ['cantidad' => 0, 'total' => 0], 'p2' => ['cantidad' => 0, 'total' => 0]];
    @endphp
    <div class="table-wrapper">
        <div class="category-title">{{ $titulo }}</div>
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
                @foreach ($items as $item)
                    @php
                        $subtotales['p1']['cantidad'] += $item['p1']['cantidad_total'] ?? 0;
                        $subtotales['p1']['total'] += $item['p1']['total'] ?? 0;
                        $subtotales['p2']['cantidad'] += $item['p2']['cantidad_total'] ?? 0;
                        $subtotales['p2']['total'] += $item['p2']['total'] ?? 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $item['item_base']['articulo'] }}</strong><br><small
                                class="text-muted">{{ $item['item_base']['unidad_medida'] }}</small></td>
                        <td class="text-right">{{ number_format($item['p1']['cantidad_total'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($item['p1']['total'] ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($item['p2']['cantidad_total'] ?? 0, 2) }}</td>
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
                    <td><strong>Totales {{ $titulo }}</strong></td>
                    <td class="text-right">{{ number_format($subtotales['p1']['cantidad'], 2) }}</td>
                    <td class="text-right">{{ number_format($subtotales['p1']['total'], 2) }}</td>
                    <td class="text-right">{{ number_format($subtotales['p2']['cantidad'], 2) }}</td>
                    <td class="text-right">{{ number_format($subtotales['p2']['total'], 2) }}</td>
                    <td class="text-right">
                        {{ number_format($subtotales['p1']['cantidad'] - $subtotales['p2']['cantidad'], 2) }}</td>
                    <td class="text-right">
                        {{ number_format($subtotales['p1']['total'] - $subtotales['p2']['total'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @php
        // Acumulamos a los totales de etapa y generales, pasados por referencia
        $totalesEtapa['p1'] += $subtotales['p1']['total'];
        $totalesEtapa['p2'] += $subtotales['p2']['total'];
        $totalesGenerales['p1'][$tipo] += $subtotales['p1']['total'];
        $totalesGenerales['p2'][$tipo] += $subtotales['p2']['total'];
    @endphp
@endif
