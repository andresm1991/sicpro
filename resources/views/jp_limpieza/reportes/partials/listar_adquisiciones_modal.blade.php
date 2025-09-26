<div class="table-responsive">
    <table class="table table-bordered table-striped table-sm">
        <thead>
            <tr>
                <th>Orden Nro.</th>
                <th>Fecha Pedido</th>
                <th>Proyecto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($query as $categoria => $items)
                @forelse ($items as $item)
                    <tr>
                        <td class="align-middle">
                            <a href="{{ route('pdf.jp.limpieza.adquisicion', $item['id']) }}" target="_blank"
                                rel="noopener noreferrer">
                                {{ $item['numero'] }}
                            </a>
                        </td>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</td>
                        <td class="align-middle">
                            @if (isset($item['proyecto_id']) && is_numeric($item['proyecto_id']))
                                {{ $item['proyecto']['nombre_proyecto'] }}
                            @else
                                Gastos Administrativos (sin proyecto)
                            @endif
                        </td>
                        <td class="align-middle text-capitalize">{{ $item['estado'] }}</td>
                    </tr>
                @empty
                @endforelse
            @empty
                <div class="alert alert-info text-center">
                    No se encontraron registros para los filtros seleccionados.
                </div>
            @endforelse
        </tbody>
    </table>
</div>
