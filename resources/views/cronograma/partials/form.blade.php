<div class="row">
    <div class="col-12">
        @include('partials.alerts')
    </div>


    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Día</th>
                    <th scope="col">Rubro</th>
                    <th scope="col">Observación</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rubroActual = null; // Rastrea el rubro actual
                @endphp

                @foreach ($diasSemana as $dia)
                    @php
                        $rubroDia = $rubrosPorDia[$dia] ?? null;
                        $esInicioRubro = false;

                        // Detectar el inicio de un nuevo rubro
                        if ($rubroDia && $rubroDia['id'] !== ($rubroActual['id'] ?? null)) {
                            $rubroActual = $rubroDia;
                            $esInicioRubro = true;
                        }

                        // Contar cuántos días abarca este rubro para el rowspan
                        $rowspan = 1;
                        if ($esInicioRubro) {
                            $rowspan = count($rubrosAgrupados[$rubroDia['id']]['dias']);
                        }

                        // Obtener la observación del día (si existe)
                        $observacion = $rubroDia['observacion'] ?? '';
                    @endphp

                    <tr>
                        <!-- Nombre del día con checkbox -->
                        <td class="align-middle font-weight-bold">
                            <div class="form-check">
                                <input name="dias[{{ $dia }}][checked]" class="form-check-input"
                                    type="checkbox" value="{{ $rubroDia['id'] ?? '' }}" id="check_{{ $dia }}"
                                    {{ isset($rubroDia) ? 'checked' : '' }} data-rubro-id="{{ $rubroDia['id'] ?? '' }}">
                                <label class="form-check-label" for="check_{{ $dia }}">
                                    {{ ucfirst($dia) }}
                                </label>
                            </div>
                        </td>

                        <!-- Rubro (solo en la primera fila del grupo) -->
                        @if ($esInicioRubro)
                            <td rowspan="{{ $rowspan }}" class="align-middle text-center font-weight-bold">
                                {{ $rubroDia['nombre'] }}
                            </td>
                        @elseif(!$rubroDia)
                            <td class="align-middle text-center font-weight-bold"></td>
                        @endif

                        <!-- Observación -->
                        <td class="align-middle">
                            {!! Form::text("dias[$dia][observacion]", $observacion, [
                                'class' => 'form-control',
                                'placeholder' => 'Ingresa la observación (opcional)',
                            ]) !!}
                        </td>
                    </tr>

                    {!! Form::hidden("dias[$dia][rubo]", $rubroDia['id'] ?? '') !!}
                @endforeach
            </tbody>
        </table>
    </div>
</div>
