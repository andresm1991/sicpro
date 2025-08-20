@php
    // Obtener la configuración desde config/file_inputs.php
    $fileInputs_vcontratista = config('file_inputs_proyectos.valores_contratista');
@endphp
<div class="row">
    @foreach ($fileInputs_vcontratista as $input)
        @php
            // Filtrar archivos para el tipo de archivo actual
            $archivosFiltrados = $proyecto->archivos_proyecto->filter(function ($archivo) use ($input) {
                return strtolower($input['id']) == 'fileinput' . strtolower($archivo->tipo_archivo);
            });
        @endphp

        <div class="col-sm-3">
            <div class="form-group">
                <div class="file-input-container">
                    <div class="row" id="{{ $input['id'] }}">
                        <div class="col-sm-2 col-2 d-flex justify-content-center align-items-center">
                            <i class="fa-regular fa-upload fa-lg"></i>
                        </div>
                        <div class="col-sm-10 col-10">
                            <input type="file" id="{{ $input['id'] }}" class="multiFileInput"
                                name="archivos[{{ $input['id'] }}][]" multiple>
                            <label for="{{ $input['id'] }}" class="col-form-label"> {{ $input['label'] }}</label>
                        </div>
                    </div>
                </div>
                {!! $errors->first('archivos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                <h6 class="mt-4">Archivos seleccionados:</h6>

                <ul class="file-list" id="fileList-actuales-{{ $input['id'] }}">
                    @foreach ($archivosFiltrados as $archivo)
                        <li>
                            {{ $archivo->nombre }}<span class="remove-file-actuales" style="cursor: pointer;">
                                <i class="fa-solid fa-trash-can"></i></span>
                            {{ Form::hidden('archivos_actuales[path][]', $archivo->ruta_archivo) }}
                            {{ Form::hidden('archivos_actuales[id][]', $archivo->id) }}
                        </li>
                    @endforeach
                </ul>
                <ul class="file-list" id="fileList-{{ $input['id'] }}" data-input-id="{{ $input['id'] }}"></ul>

                <span class="text-secondary" id="sin-archivos-{{ $input['id'] }}"
                    {{ $archivosFiltrados->isEmpty() ? '' : 'style=display:none;' }}>
                    No existen archivos seleccionados.
                </span>
            </div>
        </div>
    @endforeach
</div>
