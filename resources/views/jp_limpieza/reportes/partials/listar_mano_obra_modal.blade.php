<div class="table-responsive">
    <table class="table  table-bordered table-striped table-sm">
        <thead>
            <tr>
                <th>#</th>
                <th>fecha desde</th>
                <th>Fecha hasta</th>
                <th>Tipo</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($query as $index => $item)
                <tr class="mano-obra fila-clicable" data-url="{{ route('pdf.jp.limpieza.mano.obra', $item->id) }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="align-middle">
                        {{ \Carbon\Carbon::parse($item->fecha_desde)->format('d/m/Y') }}
                    </td>
                    <td class="align-middle">
                        {{ \Carbon\Carbon::parse($item->fecha_hasta)->format('d/m/Y') }}
                    </td>
                    <td class="align-middle text-capitalize">{{ $item->tipo }}</td>
                    <td class="align-middle text-right">$ {{ $item->total_recibir_formatted }}</td>
                </tr>
            @empty
                <div class="alert alert-info text-center">
                    No se encontraron registros para los filtros seleccionados.
                </div>
            @endforelse
        </tbody>
    </table>
</div>
