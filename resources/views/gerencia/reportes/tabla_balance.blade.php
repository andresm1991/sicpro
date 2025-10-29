<div class="responsive">
    <table class="table table-bordered table-sm">
        <thead class="thead-dark">
            <tr>
                <th style="width: 40%;">Proyecto</th>
                <th class="text-right">Total Ingresos</th>
                <th class="text-right">Total Gastos</th>
                <th class="text-right">Utilidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($query as $item)
                <tr>
                    <td>
                        @if (isset($item['proyecto_id']) && is_numeric($item['proyecto_id']))
                            <a href="{{ route('proyecto.informacion.general', ['tipo' => $item['proyecto_tipo'], 'tipo_id' => $item['proyecto_tipo_id'], 'proyecto' => $item['proyecto_id']]) }}"
                                target="_blank" rel="noopener noreferrer">{{ $item['proyecto_nombre'] }}</a>
                        @else
                            {{ $item['proyecto_nombre'] }}
                        @endif
                    </td>
                    <td class="text-right">$ {{ number_format($item['total_ingresos'], 4) }}</td>
                    <td class="text-right">$ {{ number_format($item['total_gastos'], 4) }}</td>
                    <td class="text-right @if ($item['utilidad'] < 0) text-danger font-weight-bold @endif">
                        $ {{ number_format($item['utilidad'], 4) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="aling-middle" colspan="4">No existen datos para mostrar</td>
                </tr>
            @endforelse
        </tbody>
        @if ($query->isNotEmpty())
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
