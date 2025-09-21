@forelse ($reportData as $categoria => $items)
    {{-- Título de la categoría --}}
    <h4 class="mt-4 mb-3 text-primary" style="font-size: 14; color:rgb(25, 25, 124);">{{ $categoria }}
    </h4>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">

            {{-- SECCIÓN PARA ADQUISICIONES (MATERIALES, SERVICIOS, ETC.) --}}
            @if ($categoria !== 'Mano de Obra' && $categoria !== 'Contratistas')
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Número</th>
                        <th>Proyecto</th>
                        <th>Proveedor</th>
                        <th>Estado</th>
                        <th>Factura</th>
                        <th>Forma Pago</th>
                        <th>Necesidades</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</td>
                            <td>{{ $item['numero'] }}</td>
                            <td>{{ $item['proyecto']['nombre_proyecto'] ?? 'N/A' }}</td>
                            <td>{{ $item['proveedor']['razon_social'] ?? 'N/A' }}</td>
                            <td>{{ $item['estado'] }}</td>
                            <td>{{ $item['nro_factura'] ?? 'N/A' }}</td>
                            <td>{{ $item['forma_pago']['descripcion'] ?? 'N/A' }}</td>
                            {{-- Mapear los detalles para obtener las necesidades --}}
                            <td>{{ collect($item['detalles'])->pluck('necesidad')->implode(', ') }}
                            </td>
                            <td class="text-right">$ {{ number_format($item['total_general'], 4) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-weight-bold">
                    <tr>
                        <td colspan="8" class="text-right">total {{ $categoria }}:</td>
                        <td class="text-right">$
                            {{ number_format(collect($items)->sum('total_general'), 4) }}</td>
                    </tr>
                </tfoot>

                {{-- SECCIÓN PARA MANO DE OBRA --}}
            @elseif($categoria === 'Mano de Obra')
                <thead class="thead-dark">
                    <tr>
                        <th>Proyecto</th>
                        <th>Tipo</th>
                        <th>Fecha Desde</th>
                        <th>Fecha Hasta</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['proyecto']['nombre_proyecto'] ?? 'N/A' }}</td>
                            <td>{{ $item['tipo'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($item['fecha_desde'])->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item['fecha_hasta'])->format('d/m/Y') }}</td>
                            <td class="text-right">$ {{ $item['total_recibir_formatted'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-weight-bold">
                    <tr>
                        <td colspan="4" class="text-right">total {{ $categoria }}:</td>
                        <td class="text-right">$
                            {{ number_format(collect($items)->sum('total_recibir'), 4) }}</td>
                    </tr>
                </tfoot>

                {{-- SECCIÓN PARA CONTRATISTAS --}}
            @elseif($categoria === 'Contratistas')
                <thead class="thead-dark">
                    <tr>
                        <th>Proyecto</th>
                        <th>Proveedor</th>
                        <th>Categoría</th>
                        <th>Total Contratado</th>
                        <th>Total Pagado</th>
                        <th>Saldo</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['proyecto']['nombre_proyecto'] ?? 'N/A' }}</td>
                            <td>{{ $item['proveedor']['razon_social'] ?? 'N/A' }}</td>
                            <td>{{ $item['categoria_proveedor']['descripcion'] ?? 'N/A' }}</td>
                            <td class="text-right">$ {{ number_format($item['total_contratado'], 4) }}
                            </td>
                            <td class="text-right">$ {{ number_format($item['total_pagado'], 4) }}</td>
                            <td class="text-right">$ {{ number_format($item['total_pendiente'], 4) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-weight-bold">
                    <tr>
                        <td colspan="3" class="text-right">Subtotal {{ $categoria }}:</td>
                        <td class="text-right">$ {{ number_format(collect($items)->sum('monto'), 4) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

@empty
    {{-- Mensaje si no se encuentra ningún registro en ninguna categoría --}}
    <div class="alert alert-info text-center">
        No se encontraron registros para los filtros seleccionados.
    </div>
@endforelse

{{-- Mostrar el GRAN TOTAL al final de todo el reporte --}}
@if (!empty($reportData))
    <div class="text-right mt-4">
        <h3>Total general: <span class="badge badge-success">$ {{ number_format($grandTotal, 4) }}</span>
        </h3>
    </div>
@endif
