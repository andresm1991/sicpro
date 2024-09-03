@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <h4 class="col ">Información general</h4>
                            <div class="col-sm-1 col text-right">
                                <a href="{{ route('proyecto.edit', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]) }}"
                                    class="btn btn-dark btn-options d-flex justify-content-center align-items-center">Editar</a>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Información
                                        general</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab">Archivos
                                        Operativos</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Archivos
                                        Administrativos</a>
                                </li>
                            </ul><!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                    <div class="row p-4">
                                        {{-- Información --}}
                                        <div class="col-sm-8">
                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Nombre del Proyecto </label>
                                                        {{ Form::label('nombre_proyecto', $proyecto->nombre_proyecto, ['class' => 'form-control text-capitalize']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Nombre del propietario </label>
                                                        {{ Form::label('propietario', $proyecto->nombre_propietario, ['class' => 'form-control text-capitalize']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Ubicación</label>
                                                        {{ Form::label('ubicacion_proyecto', $proyecto->ubicacion, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Dirección</label>
                                                        {{ Form::label('direccion', $proyecto->direccion, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Tipo de proyecto </label>
                                                        {{ Form::label('tipo_proyecto', $proyecto->tipo_proyecto->descripcion, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Correo </label>
                                                        {{ Form::label('correo', $proyecto->correo, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Teléfono </label>
                                                        {{ Form::label('telefono', $proyecto->telefono, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Total área del lote m² </label>
                                                        {{ Form::label('area_lote', $proyecto->area_lote, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Total área de construcción m² </label>
                                                        {{ Form::label('area_construccion', $proyecto->area_construccion, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Número de unidades </label>
                                                        {{ Form::label('numero_unidades', $proyecto->numero_unidades, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Área de lote por unidad m² </label>
                                                        {{ Form::label('area_lote_unidad', $proyecto->area_lote_unidad, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Área de construcción por unidad m²
                                                        </label>
                                                        {{ Form::label('area_construccion_unidad', $proyecto->area_construccion_unidad, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Presupuesto total </label>
                                                        {{ Form::label('presupuesto', $proyecto->presupuesto_total, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Presupuesto por unidad </label>
                                                        {{ Form::label('presupuesto_unidad', $proyecto->presupuesto_unidad, ['class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Fecha de inicio </label>
                                                        {{ Form::label('fecha_inicio', dateFormat('Y-m-d', 'd-m-Y', $proyecto->fecha_inicio), ['class' => 'form-control datepicker']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Fecha de finalización </label>
                                                        {{ Form::label('fecha_fin', dateFormat('Y-m-d', 'd-m-Y', $proyecto->fecha_finalizacion), ['class' => 'form-control datepicker']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Observaciones</label>
                                                {{ Form::textarea('observaciones', $proyecto->observacion, ['class' => 'form-control', 'disabled', 'rows' => '5', 'style' => 'background-color:transparent']) }}
                                            </div>
                                        </div>
                                        {{-- Files --}}
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <div class="file-input-container">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h5 class="col-form-label">Portada del Proyecto</h5>
                                                        </div>
                                                        <div class="col-lg-12 file-input-wrapper">
                                                            <img src="{{ $proyecto->portada ? showImage($proyecto->portada) : asset('images/no-fotos.png') }}"
                                                                class="card-img-top" id="preview-portada" alt="portada">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- .row -->
                                </div><!-- Tab pane 1 -->
                                <div class="tab-pane" id="tabs-2" role="tabpanel">
                                    @php
                                        // Obtener la configuración desde config/file_inputs.php
                                        $fileInputs = config('file_inputs_proyectos.operativos');
                                    @endphp
                                    <div class="row">
                                        @foreach ($fileInputs as $input)
                                            @php
                                                // Filtrar archivos para el tipo de archivo actual
                                                $archivosFiltrados = $proyecto->archivos_proyecto->filter(function (
                                                    $archivo,
                                                ) use ($input) {
                                                    return strtolower($input['id']) ==
                                                        'fileinput' . strtolower($archivo->tipo_archivo);
                                                });
                                            @endphp

                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <div class="file-input-container">
                                                        <div class="row" id="{{ $input['id'] }}">
                                                            <div
                                                                class="col-sm-2 col-2 d-flex justify-content-center align-items-center">
                                                                <i class="fa-regular fa-upload fa-lg"></i>
                                                            </div>
                                                            <div class="col-sm-10 col-10">
                                                                <input type="file" id="{{ $input['id'] }}"
                                                                    class="multiFileInput"
                                                                    name="archivos[{{ $input['id'] }}][]" multiple>
                                                                <label for="{{ $input['id'] }}" class="col-form-label">
                                                                    {{ $input['label'] }}</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <h6 class="mt-4">Archivos seleccionados:</h6>

                                                    <ul class="file-list" id="fileList-actuales-{{ $input['id'] }}">
                                                        @foreach ($archivosFiltrados as $archivo)
                                                            <li>
                                                                {{ $archivo->nombre }} <button class="btn btn-dark"
                                                                    form="{{ 'form-download-files-' . $archivo->id }}"><i
                                                                        class="fa-regular fa-download download"></i></button>
                                                            </li>
                                                            {{ Form::open(['route' => ['proyecto.download.files'], 'id' => 'form-download-files-' . $archivo->id]) }}
                                                            {{ Form::hidden('archivo', $archivo->ruta_archivo) }}
                                                            {{ Form::close() }}
                                                        @endforeach
                                                    </ul>

                                                    <span class="text-secondary" id="sin-archivos-{{ $input['id'] }}"
                                                        {{ $archivosFiltrados->isEmpty() ? '' : 'style=display:none;' }}>
                                                        No existen archivos seleccionados.
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div><!-- Tab pane 2 -->
                                <div class="tab-pane" id="tabs-3" role="tabpanel">
                                    @php
                                        // Obtener la configuración desde config/file_inputs.php
                                        $fileInputsAdministrativos = config('file_inputs_proyectos.administrativos');
                                    @endphp
                                    <div class="row">
                                        @foreach ($fileInputsAdministrativos as $input)
                                            @php
                                                // Filtrar archivos para el tipo de archivo actual
                                                $archivosFiltrados = $proyecto->archivos_proyecto->filter(function (
                                                    $archivo,
                                                ) use ($input) {
                                                    return strtolower($input['id']) ==
                                                        'fileinput' . strtolower($archivo->tipo_archivo);
                                                });
                                            @endphp

                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <div class="file-input-container">
                                                        <div class="row" id="{{ $input['id'] }}">
                                                            <div
                                                                class="col-sm-2 col-2 d-flex justify-content-center align-items-center">
                                                                <i class="fa-regular fa-upload fa-lg"></i>
                                                            </div>
                                                            <div class="col-sm-10 col-10">
                                                                <input type="file" id="{{ $input['id'] }}"
                                                                    class="multiFileInput"
                                                                    name="archivos[{{ $input['id'] }}][]" multiple>
                                                                <label for="{{ $input['id'] }}" class="col-form-label">
                                                                    {{ $input['label'] }}</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <h6 class="mt-4">Archivos seleccionados:</h6>

                                                    <ul class="file-list" id="fileList-actuales-{{ $input['id'] }}">
                                                        @foreach ($archivosFiltrados as $archivo)
                                                            <li>
                                                                {{ $archivo->nombre }} <button class="btn btn-dark"
                                                                    form="{{ 'form-download-files-' . $archivo->id }}"><i
                                                                        class="fa-regular fa-download download"></i></button>
                                                            </li>
                                                            {{ Form::open(['route' => ['proyecto.download.files'], 'id' => 'form-download-files-' . $archivo->id]) }}
                                                            {{ Form::hidden('archivo', $archivo->ruta_archivo) }}
                                                            {{ Form::close() }}
                                                        @endforeach
                                                    </ul>
                                                    <ul class="file-list" id="fileList-{{ $input['id'] }}"
                                                        data-input-id="{{ $input['id'] }}"></ul>

                                                    <span class="text-secondary" id="sin-archivos-{{ $input['id'] }}"
                                                        {{ $archivosFiltrados->isEmpty() ? '' : 'style=display:none;' }}>
                                                        No existen archivos seleccionados.
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div><!-- Tab pane 3 -->
                            </div><!-- Tab content -->
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>

@endsection
