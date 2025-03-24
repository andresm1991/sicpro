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
                    $rowspanCount = 0; // Contador para el rowspan
                    $rowspanMap = []; // Mapa para almacenar los rowspan de cada rubro
                @endphp

                <!-- Calcular los rowspan para cada rubro -->
                @foreach ($diasSemana as $dia)
                    @php
                        $rubrosDia = $rubrosPorDia[$dia] ?? ['rubros' => '', 'observaciones' => ''];
                        $rubroNombre = $rubrosDia['rubros'];

                        if (!isset($rowspanMap[$rubroNombre])) {
                            $rowspanMap[$rubroNombre] = 0;
                        }
                        $rowspanMap[$rubroNombre]++;
                    @endphp
                @endforeach

                @php
                    $rubroActual = null; // Reiniciar el rubro actual para la iteración principal
                @endphp

                @foreach ($diasSemana as $dia)
                    @php
                        $rubrosDia = $rubrosPorDia[$dia] ?? ['rubros' => '', 'observaciones' => ''];
                        $rubroNombre = $rubrosDia['rubros'];
                        $esInicioRubro = false;

                        // Detectar el inicio de un nuevo rubro
                        if ($rubroNombre !== ($rubroActual ?? null)) {
                            $rubroActual = $rubroNombre;
                            $esInicioRubro = true;
                        }

                        // Obtener la observación del día (si existe)
                        $observacion = $rubrosDia['observaciones'];
                    @endphp

                    <tr>
                        <!-- Nombre del día con checkbox -->
                        <td class="align-middle font-weight-bold">
                            <div class="form-check">
                                <input name="dias[{{ $dia }}][checked]" class="form-check-input"
                                    type="checkbox" value="{{ $rubroNombre ? 'checked' : '' }}"
                                    id="check_{{ $dia }}" {{ $rubroNombre ? 'checked' : '' }}>
                                <label class="form-check-label" for="check_{{ $dia }}">
                                    {{ ucfirst($dia) }}
                                </label>
                            </div>
                        </td>

                        <!-- Rubro (solo en la primera fila del grupo) -->
                        @if ($esInicioRubro && $rubroNombre)
                            <td rowspan="{{ $rowspanMap[$rubroNombre] }}"
                                class="align-middle text-center font-weight-bold">
                                {{ $rubroNombre }}
                            </td>
                        @elseif(!$rubroNombre)
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

                    {!! Form::hidden("dias[$dia][rubo]", $rubroNombre) !!}
                @endforeach
            </tbody>
        </table>
    </div>
</div>
