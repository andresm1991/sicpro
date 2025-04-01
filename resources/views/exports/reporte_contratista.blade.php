<table>
    <thead>
        <tr>
            <th>fecha</th>
            <th>proyecto</th>
            <th>proveedor</th>
            <th>producto</th>
            <th>plazo semanas</th>
            <th>estapa</th>
            <th>estado</th>
            <th>total</th>
            <th>abonado</th>
            <th>saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $item)
            <tr>
                <td>{{ $item['contratista']->fecha }}</td>
                <td>{{ $item['contratista']->proyecto->nombre_proyecto }}</td>
                <td>{{ isset($item['contratista']->proveedor->razon_social) ? $item['contratista']->proveedor->razon_social : $item['contratista']->proveedor->apellidos . ' ' . $item['contratista']->proveedor->nombres }}
                </td>
                <td>{{ $item['contratista']->articulo->descripcion }}</td>
                <td>{{ $item['contratista']->plazo_semanas }}</td>
                <td>{{ $item['contratista']->etapa->descripcion }}</td>
                <td>{{ $item['contratista']->estado->descripcion }}</td>
                <td>{{ $item['total'] }}</td>
                <td>{{ $item['total_pagado'] }}</td>
                <td>{{ $item['saldo'] }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="9" style="text-align: right;"><strong>Total General:</strong></td>
            <td>{{ number_format($totalGeneral, 4) }}</td>
        </tr>
        <tr>
            <td colspan="9" style="text-align: right;"><strong>Total Pagado:</strong></td>
            <td>{{ number_format($totalPagado, 4) }}</td>
        </tr>
        <tr>
            <td colspan="9" style="text-align: right;"><strong>Total Saldo:</strong></td>
            <td>{{ number_format($totalSaldos, 4) }}</td>
        </tr>
    </tfoot>
</table>
