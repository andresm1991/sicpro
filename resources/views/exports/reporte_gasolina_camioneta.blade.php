<h1>Reporte de Gasolina para Camioneta</h1>
<table>
    <thead>
        <tr>
            <th>FECHA</th>
            <th>DIAS</th>
            <th>KILOMETRAJE DE CARGA</th>
            <th>VALOR DE CARGA</th>
            <th>GALONES DE CARGA</th>
            <th>KILOMETRAJE ANTERIOR</th>
            <th>KILOMETRAJE RECORRIDO</th>
            <th>KILOMETRAJE /GALON</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $item)
            <tr>
                <td>{{ $item['fecha'] }}</td>
                <td>{{ $item['dias'] }}</td>
                <td>{{ $item['km_carga'] }}</td>
                <td>{{ $item['valor'] }}</td>
                <td>{{ $item['galones'] }}</td>
                <td>{{ $item['km_anterior'] }}</td>
                <td>{{ $item['km_recorrido'] }}</td>
                <td>{{ $item['km_galon'] }}</td>
            </tr>
        @endforeach
    </tbody>

</table>
