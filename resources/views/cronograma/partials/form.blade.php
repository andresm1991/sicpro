<div class="row">
    <div class="col-12">
        @include('partials.alerts')
    </div>


    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Rubro</th>

                    @foreach ($diasSemana as $dia)
                        <th>{{ ucfirst($dia) }}</th>
                    @endforeach
                    <th scope="col">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tablaDatos as $rubro => $dias)
                    <tr>
                        <td>{{ $rubro }}</td>
                        @foreach ($diasSemana as $dia)
                            <td class="{{ $dias[$dia]['id'] ? 'bg-success' : '' }}"></td>
                            {!! Form::hidden("dias[$dia]['id']", $dias[$dia]['id'] ?? 'null') !!}
                        @endforeach
                        <td class="aling-middle">{{ implode(', ', array_column($tablaDatos, 'observacion')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
