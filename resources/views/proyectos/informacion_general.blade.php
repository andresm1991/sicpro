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
                                        <label class="col-form-label">Área del lote m² </label>
                                        {{ Form::label('area_lote', $proyecto->area_lote, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="col-form-label">Área de construcción m² </label>
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
                                        <label class="col-form-label">Área de construcción por unidad m² </label>
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
                            <h3>Subir archivos</h3>
                            <div class="form-group">
                                <div class="file-input-container" style="justify-content:start">
                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="col-form-label">Portada del Proyecto</h5>
                                        </div>
                                        <div class="col-lg-12">
                                            <img src="{{ showImage($proyecto->portada) }}" class="card-img-top"
                                                alt="portada">
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-12 text-left">
                                        <h5 class="col-form-label">Adjuntos del Proyecto</h5>
                                    </div>
                                    <div class="col-lg-12">
                                        @if ($proyecto->archivos_proyecto->count() > 0)
                                            <ul class="file-list">
                                                @foreach ($proyecto->archivos_proyecto as $archivo)
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
                                        @else
                                            <span class="text-secondary">No existen adjunto en este proyecto.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>

@endsection
