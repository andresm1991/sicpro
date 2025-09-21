<div class="table-responsive">
    <table class="table table-bordered table-striped table-sm">
        <thead class="thead-dark">
            <tr>
                <th style="width: 40%;">Proyecto</th>
                <th class="text-right">Total Ingresos</th>
                <th class="text-right">Total Gastos</th>
                <th class="text-right">Utilidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData as $item)
                <tr>
                    <td>{{ $item['proyecto_nombre'] }}</td>
                    <td class="text-right">$ {{ number_format($item['total_ingresos'], 4) }}</td>
                    <td class="text-right">$ {{ number_format($item['total_gastos'], 4) }}</td>
                    <td class="text-right @if ($item['utilidad'] < 0) text-danger font-weight-bold @endif">
                        $ {{ number_format($item['utilidad'], 4) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No se encontraron proyectos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($reportData->isNotEmpty())
            <tfoot class="bg-light font-weight-bold">
                <tr>
                    <th>TOTALES GENERALES</th>
                    <th class="text-right">$ {{ number_format($totalIngresosGeneral, 4) }}</th>
                    <th class="text-right">$ {{ number_format($totalGastosGeneral, 4) }}</th>
                    <th class="text-right @if ($utilidadGeneral < 0) text-danger @endif">
                        $ {{ number_format($utilidadGeneral, 4) }}
                    </th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
