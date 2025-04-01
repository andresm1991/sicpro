<!-- filepath: c:\laragon\www\sicpro\resources\views\exports\reporte_adquisiciones.blade.php -->
<table>
    <thead>
        <tr>
            <th>fecha</th>
            <th>numero</th>
            <th>proyecto</th>
            <th>etapa</th>
            <th>estado</th>
            <th>tipo adquisicion</th>
            <th>factura</th>
            @if ($producto != '')
                <th>cantidad</th>
            @endif
            <th>forma pago</th>
            <th>total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $item)
            <tr>
                <td>{{ $item['adquisicion']->fecha }}</td>
                <td>{{ $item['adquisicion']->numero }}</td>
                <td>{{ $item['adquisicion']->proyecto->nombre_proyecto }}</td>
                <td>{{ $item['adquisicion']->etapa->descripcion }}</td>
                <td>{{ $item['adquisicion']->estado != 'Completado' ? 'Pendiente' : 'Completado' }}</td>
                <td>{{ $item['adquisicion']->tipo_adquisicion }}</td>
                <td>{{ $item['adquisicion']->factura ?? '' }}</td>
                @if ($producto != '')
                    <td>{{ $item['cantidad'] }}</td>
                @endif
                <td>
                    @isset($item['adquisicion']->orden_recepcion)
                        {{ $item['adquisicion']->orden_recepcion->forma_pago->descripcion }}
                    @endisset
                </td>
                <td>{{ $item['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="{{ $producto != '' ? 9 : 8 }}" style="text-align: right;"><strong>Total General:</strong></td>
            <td>{{ number_format($totalGeneral, 4) }}</td>
        </tr>
    </tfoot>
</table>
