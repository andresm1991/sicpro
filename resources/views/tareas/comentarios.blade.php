@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-8 p-0">
                                {{ Form::text('titulo', old('titulo', $tarea->titulo), ['class' => 'font-weight-bold text-dark form-control form-control-plaintext text-uppercase']) }}

                            </div>

                            <div class="col-sm-auto">
                                <label class="col-form-label">Estado</label>
                            </div>
                            <div class="col-sm-2">
                                <select name="estado" data-tags="false" data-placeholder="Select an option"
                                    data-allow-clear="false" class="form-control" data-width = "150"
                                    data-minimum-results-for-search="Infinity">
                                    @foreach (getEstadosTarea() as $index => $name)
                                        <option value="{{ $index }}"
                                            {{ $tarea->estado_id == $index ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body ">
                    <form autocomplete="off" enctype="multipart/form-data" id="form_comentario_tarea">
                        {{ Form::hidden('tarea_id') }}
                        <div class="row">
                            <div class="col-12 form-group">
                                <textarea name="descripcion" id="descripcion" class="form-control text-uppercase form-control-plaintext">{{ $tarea->descripcion }}</textarea>
                            </div>

                            <div class="col-12 form-group">
                                {{ Form::label('', 'Colaboradores', ['class' => 'col-form-label']) }}
                                {{ Form::select('list_usuarios[]', usuariosPluck(true), 0, ['class' => 'select2-basic-single form-control', 'id' => 'list_usuarios', 'multiple' => 'multiple', 'data-placeholder' => 'SELECCIONE USUARIOS']) }}
                            </div>

                            @can('agenda.editar')
                                <div class="col-12 form-group">
                                    <label class="col-form-label">Categoria</label>
                                    {{ Form::select('categoria_tarea', categoriasAgenda(), '', ['class' => 'select2-tag form-control', 'id' => 'categoria_tarea', 'data-placeholder' => 'SELECCIONE OPCIÓN']) }}
                                </div>
                            @endcan


                            <div class="col-12 form-group">
                                {{ Form::label('', 'Comentarios', ['class' => 'col-form-label']) }}
                                {{ Form::textarea('comentario', '', ['class' => 'form-control', 'id' => 'comentario', 'placeholder' => 'Añadir un comentario...', 'rows' => '3']) }}
                            </div>
                        </div>

                        <button type="button" class="btn btn-dark mb-4" id="guardar-comentario">Agregar</button>
                    </form>
                    <div class="row">
                        <div class="col-12" id="comentarios">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/tareas_scripts.js') }}" type="module"></script>
@endsection
