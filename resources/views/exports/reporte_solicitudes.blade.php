<table id="table-resumen">
    <tr>
        <th>colaborador</th>
        @if ($tipo_solicitud != 'reposiciones_global' && $tipo_solicitud != 'reposiciones_detallado')
            <th>fecha solicitud</th>
            @if (strtoupper($tipo_solicitud) == 'AUSENCIA')
                <th>fecha solicitada</th>
                <th>tiempo total</th>
                <th>tipo</th>
                <th>recuperable</th>
            @endif
            <th>estado</th>
            <th>detalle</th>
        @elseif($tipo_solicitud === 'reposiciones_global')
            <th>Tiempo solicitado</th>
            <th>Tiempo recuperado</th>
        @else
            <th>fecha</th>
            <th>hora desde</th>
            <th>hora fin</th>
            <th>tiempo total</th>
            <th>estado</th>
            <th>motivo</th>
        @endif
    </tr>
    <tbody>
        @forelse ($query as $items)
            @if ($tipo_solicitud == 'reposiciones_detallado')
                @foreach ($items->reposiciones as $reposiciones)
                    <tr>
                        <td>{{ $reposiciones->usuario->nombre }}</td>
                        <td>{{ $reposiciones->fecha }}</td>
                        <td>{{ $reposiciones->hora_desde }}</td>
                        <td>{{ $reposiciones->hora_hasta }}</td>
                        <td>{{ $reposiciones->total }}</td>
                        <td>{{ $reposiciones->estado->descripcion }}</td>
                        <td>{{ $reposiciones->detalle }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>
                        {{ $items->usuario->nombre }}
                    </td>
                    @if ($tipo_solicitud != 'reposiciones_global' && $tipo_solicitud != 'reposiciones_detallado')
                        <td>
                            {{ $items->fecha_solicitud }}
                        </td>
                        @if (strtoupper($tipo_solicitud) == 'AUSENCIA')
                            <td>
                                {{ $items->fecha_desde }} hasta {{ $items->fecha_hasta }}
                            </td>
                            <td>
                                {{ $items->total_tiempo }}
                            </td>
                            <td>
                                {{ $items->tipo_solicitud->descripcion }}
                            </td>

                            <td class="text-center">
                                {{ $items->recuperable ? 'SI' : 'NO' }}
                            </td>
                        @endif
                        <td>
                            {{ $items->estado_solicitud->descripcion }}
                        </td>
                        <td>
                            {{ $items->detalle }}
                        </td>
                    @elseif($tipo_solicitud == 'reposiciones_global')
                        <td>{{ $items->tiempo_acumulado_formateado }}</td>
                        <td>{{ $items->tiempo_recuperado_formateado }}</td>
                    @endif
                </tr>
            @endif
        @empty
        @endforelse
    </tbody>
</table>
