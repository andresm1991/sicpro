<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Información general</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Archivos Operativos</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Archivos Administrativos</a>
            </li>
        </ul><!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                <div class="row p-4">
                    <div class="col-sm-8">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Nombre del Proyecto <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('nombre_proyecto', old('nombre_proyecto', $proyecto->nombre_proyecto), ['class' => 'form-control text-capitalize', 'placeholder' => 'Ingrese nombre del proyecto']) }}
                                    {!! $errors->first('nombre_proyecto', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Nombre del propietario <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('propietario', old('propietario', $proyecto->nombre_propietario), ['class' => 'form-control text-capitalize', 'placeholder' => 'Ingrese nombre del propietario']) }}
                                    {!! $errors->first('propietario', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Ubicación <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('ubicacion_proyecto', old('ubicacion_proyecto', $proyecto->ubicacion), ['class' => 'form-control', 'placeholder' => 'Ingrese la ubicación del mapa']) }}
                                    {!! $errors->first('ubicacion_proyecto', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Dirección <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('direccion', old('direccion', $proyecto->direccion), ['class' => 'form-control', 'placeholder' => 'Ingrese la dirección del proyecto']) }}
                                    {!! $errors->first('direccion', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Tipo de proyecto <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::select('tipo_proyecto', $tipo_proyectos, old('tipo_proyecto', $proyecto->tipo_proyecto_id), ['class' => 'selectpicker show-tick show-menu-arrow form-control border', 'data-live-search' => 'true', 'data-style' => '', 'title' => 'Seleccione el opción', 'data-header' => 'Seleccione opción']) }}
                                    {!! $errors->first('tipo_proyecto', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Correo <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('correo', old('correo', $proyecto->correo), ['class' => 'form-control', 'placeholder' => 'Ingrese dirección de correo electrónico']) }}
                                    {!! $errors->first('correo', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Teléfono <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('telefono', old('telefono', $proyecto->telefono), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese número de teléfono', 'maxlength' => '10']) }}
                                    {!! $errors->first('telefono', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Total área del lote m² <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('area_lote', old('area_lote', $proyecto->area_lote), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el área del lote']) }}
                                    {!! $errors->first('area_lote', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Total área de construcción m² <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('area_construccion', old('area_construccion', $proyecto->area_construccion), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el área de contrucción']) }}
                                    {!! $errors->first('area_construccion', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Número de unidades</label>
                                    {{ Form::text('numero_unidades', old('numero_unidades', $proyecto->numero_unidades), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el númerdo de unidades']) }}
                                    {!! $errors->first('numero_unidades', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Área de lote por unidad m² </label>
                                    {{ Form::text('area_lote_unidad', old('area_lote_unidad', $proyecto->area_lote_unidad), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el área por unidad']) }}
                                    {!! $errors->first('area_lote_unidad', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Área de construcción por unidad
                                        m²</label>
                                    {{ Form::text('area_construccion_unidad', old('area_construccion_unidad', $proyecto->area_construccion_unidad), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el área de contrucción por unidad']) }}
                                    {!! $errors->first(
                                        'area_construccion_unidad',
                                        '<span class="help-block text-quicksand text-danger">:message</span>',
                                    ) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Presupuesto total <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('presupuesto', old('presupuesto', $proyecto->presupuesto_total), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el presupuesto']) }}
                                    {!! $errors->first('presupuesto', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Presupuesto por unidad</label>
                                    {{ Form::text('presupuesto_unidad', old('presupuesto_unidad', $proyecto->presupuesto_unidad), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el presupuesto por unidad']) }}
                                    {!! $errors->first('presupuesto_unidad', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Fecha de inicio <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('fecha_inicio', old('fecha_inicio', dateFormat('Y-m-d', 'd-m-Y', $proyecto->fecha_inicio)), ['class' => 'form-control datepicker', 'placeholder' => 'Ingrese la fecha de inicio']) }}
                                    {!! $errors->first('fecha_inicio', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Fecha de finalización <i
                                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                                    {{ Form::text('fecha_fin', old('fecha_fin', dateFormat('Y-m-d', 'd-m-Y', $proyecto->fecha_finalizacion)), ['class' => 'form-control datepicker', 'placeholder' => 'Ingrese la decha de finalización']) }}
                                    {!! $errors->first('fecha_fin', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label font-weight-bold">Observaciones</label>
                            {{ Form::textarea('observaciones', old('observaciones', $proyecto->observacion), ['class' => 'form-control', 'placeholder' => 'Ingrese observaciones', 'rows' => '5']) }}
                            {!! $errors->first('observaciones', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="col-12 p-0">
                                {!! $errors->first('portada', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                            </div>
                            <div class="file-input-container">
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="col-form-label">Portada del Proyecto</h5>
                                    </div>
                                    <div class="col-lg-12 file-input-wrapper">
                                        <img src="{{ $proyecto->portada ? showImage($proyecto->portada) : asset('images/no-fotos.png') }}"
                                            class="card-img-top" id="preview-portada" alt="portada">
                                        <label for="singleFileInput" class="file-input-label">Elige otro
                                            archivo...</label>

                                    </div>
                                </div>

                                <input type="file" id="singleFileInput" name="portada">
                            </div>
                            <small>Archivos permitidos: JPG, JPEG, PNG</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tabs-2" role="tabpanel">
                @include('proyectos.partials.archivos_operativos')
            </div>
            <div class="tab-pane" id="tabs-3" role="tabpanel">
                @include('proyectos.partials.archivos_administrativos')
            </div>
        </div>
    </div>

    {{--  
    <div class="col-sm-4">
        <h3>Subir archivos</h3>
        <div class="form-group">
            <div class="col-12 p-0">
                {!! $errors->first('portada', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
            </div>
            <div class="file-input-container">
                <div class="row">
                    <div class="col-12">
                        <h5 class="col-form-label">Portada del Proyecto</h5>
                    </div>
                    <div class="col-lg-12 file-input-wrapper">
                        <img src="{{ $proyecto->portada ? showImage($proyecto->portada) : asset('images/no-fotos.png') }}"
                            class="card-img-top" id="preview-portada" alt="portada">
                        <label for="singleFileInput" class="file-input-label">Elige otro archivo...</label>

                    </div>
                </div>

                <input type="file" id="singleFileInput" name="portada">
            </div>
            <small>Archivos permitidos: JPG, JPEG, PNG</small>
        </div>

        <div class="form-group">
            <div class="file-input-container">
                <div class="row" id="input-file">
                    <div class="col-sm-2 col-2 d-flex justify-content-center align-items-center">
                        <i class="fa-regular fa-upload fa-lg"></i>
                    </div>
                    <div class="col-sm-10 col-10">
                        <label for="fileInput" class="col-form-label"> Elige los archivo
                            relacionados con el
                            proyecto</label>
                    </div>
                </div>

                <input type="file" id="fileInput" name="files[]" multiple>
            </div>
            {!! $errors->first('archivos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
            <h6 class="mt-4">Archivos seleccionados:</h6>

            <ul class="file-list" id="fileList-actuales">
                @foreach ($proyecto->archivos_proyecto as $archivo)
                    <li>
                        {{ $archivo->nombre }}<span class="remove-file-actuales" style="cursor: pointer;"><i
                                class="fa-solid fa-trash-can"></i></span>
                        {{ Form::hidden('archivos_actuales[path]', $archivo->ruta_archivo) }}
                        {{ Form::hidden('archivos_actuales[id]', $archivo->id) }}
                    </li>
                @endforeach
            </ul>
            <ul class="file-list" id="fileList"></ul>

            <span class="text-secondary" id="sin-archivos"
                {{ $proyecto->archivos_proyecto->count() > 0 ? 'style=display:none;' : '' }}>No existen achivos
                seleccionados para el proyecto.</span>
        </div>
    </div>
    --}}
</div>
@section('scripts')
    <script src="{{ asset('js/proyectos_scripts.js') }}"></script>
@endsection
