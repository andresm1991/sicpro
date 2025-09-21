@forelse ($reportData as $categoria => $items)

    <h4 class="mt-4 mb-3 nombre-categoria">{{ $categoria }}</h4>

    <table class="table table-bordered table-sm">
        <thead class="thead-dark">
            <tr>
                @if (in_array($categoria, ['Materiales y Herramientas', 'Servicios']))
                    <th>Artículo</th>
                    <th class="text-right">Cantidad Total</th>
                @elseif ($categoria === 'Contratistas')
                    <th>Contratista (Proveedor)</th>
                    <th class="text-right">Nro. Contratos</th>
                @elseif ($categoria === 'Mano de Obra')
                    <th>Proyecto</th>
                    <th class="text-right">Nro. Planillas</th>
                @else
                    <th>Ítem</th>
                    <th class="text-right">Cantidad</th>
                @endif
                <th class="text-right">Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['articulo'] }}</td>
                    <td class="text-right">
                        {{-- Formateamos como entero si no es un artículo con decimales --}}
                        @if (in_array($categoria, ['Contratistas', 'Mano de Obra']))
                            {{ number_format($item['cantidad_total'], 0) }}
                        @else
                            {{ number_format($item['cantidad_total'], 2) }}
                        @endif
                    </td>
                    <td class="text-right">$ {{ number_format($item['monto_total'], 4) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="font-weight-bold">
            <tr>
                <td colspan="2" class="text-right">Total {{ $categoria }}:</td>
                <td class="text-right">$ {{ number_format(collect($items)->sum('monto_total'), 4) }}</td>
            </tr>
        </tfoot>
    </table>
@empty
    <div class="alert alert-info text-center">
        No se encontraron registros para los filtros seleccionados.
    </div>
@endforelse

{{-- Solo muestra el total general si se encontraron datos --}}
@if (!empty($reportData))
    <div class="text-right mt-4" style="padding-right: 15px;">
        <h3 style="font-size: 1.5rem; font-weight: bold;">
            TOTAL GENERAL:
            <span class="badge badge-success"
                style="font-size: 1.5rem; background-color: #28a745; color: white; padding: 10px;">
                $ {{ number_format($grandTotal, 4) }}
            </span>
        </h3>
    </div>
@endif
