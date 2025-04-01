<table>
    <thead>
        <tr>
            <th>proyecto</th>
            <th>semana</th>
            <th>fecha inicio</th>
            <th>fecha fin</th>
            <th>etapa</th>
            <th>actividad</th>
            <th>total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $item)
            <tr>
                <td>{{ $item['mano_obra']->proyecto->nombre_proyecto }}</td>
                <td>{{ $item['mano_obra']->semana }}</td>
                <td>{{ $item['mano_obra']->fecha_inicio }}</td>
                <td>{{ $item['mano_obra']->fecha_fin }}</td>
                <td>{{ $item['mano_obra']->etapa->descripcion }}</td>
                <td>{{ isset($item['mano_obra']->actividad) ? $item['mano_obra']->actividad->descripcion : '' }}
                </td>
                <td>{{ $item['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: right;"><strong>Total General:</strong></td>
            <td>{{ number_format($totalGeneral, 4) }}</td>
        </tr>
    </tfoot>
</table>
