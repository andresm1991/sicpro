<div class="row">
    @include('partials.alerts')

    <div class="col-12">
        <div class="form-group">
            <a href="{{ route('pdf.export.cronograma.actividades.dias', $cronograma->id) }}"
                class="btn btn-secondary btn-sm " target="_blank">
                <i class="fa-light fa-file-export"></i> Generar PDF
            </a>
        </div>

    </div>
    @php
        // Array con los días de la semana
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    @endphp

    @foreach ($dias as $dia)
        <div class="col-sm-2 day-item">
            <label class="font-weight-bold">{{ ucfirst($dia) }}</label>
            <div class="fields-container form-group" id="{{ $dia }}-fields">
                <select name="{{ $dia }}" class="form-control select2-tag actividades"
                    data-placeholder="Selecciona actividad">
                    <option value=""></option>
                    @foreach ($actividades as $index => $nombre)
                        <option value="{{ $index }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-success btn-sm add-actividad col-12" data-day="{{ $dia }}">
                <i class="fa-solid fa-plus"></i> Agregar actividad
            </button>
            <div class="row">
                <div class="col-12 mt-2 actividades-seleccionadas">
                    <!-- Aquí se mostrarán las actividades seleccionadas -->
                    @foreach ($cronograma->actividad_dias as $actividad)
                        @if ($actividad->dia == $dia)
                            <div class="d-flex align-items-center bg-dark text-white rounded p-2 mr-2 mb-2"
                                data-value="{{ $actividad->actividad_cronograma_id }}" style="max-width: 100%;">
                                <span class="flex-grow-1 text-wrap"
                                    style="word-break: break-word; overflow-wrap: break-word;">
                                    {{ $actividad->actividad_cronograma->descripcion }}
                                </span>
                                <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-activity ml-auto">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                                <input type="hidden" name="{{ $dia }}[]"
                                    value="{{ $actividad->actividad_cronograma_id }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

        </div>
    @endforeach
</div>
