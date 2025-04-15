@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container-fluid">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Agenda de tareas</h4>
                        @if (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Gerencial'))
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Filtrar agenda por</label>
                                <div class="col-sm-4">
                                    <select name="filtrar_agenda" id="filtro-agenda" class="form-control"
                                        data-placeholder = "Seleccionar opción para filtrar...">
                                        <option value=""></option>
                                        <option value="todos">todos</option>
                                        <optgroup label="categoria">
                                            @foreach (agendaCategoria() as $index => $nombre)
                                                <option value="{{ $index }}">{{ $nombre }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="colaborador">
                                            @foreach (usuariosPluck(true, false) as $index => $nombre)
                                                <option value="{{ $index }}">{{ $nombre }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>

                                </div>

                                <div class="col-sm-4">
                                    <button type="button" class="btn btn-dark" id="aplicar-filtro">Aplicar</button>
                                </div>
                            </div>
                        @endif
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-auto">
                            <div class="form-group">
                                <a href="javascript:void(0);" class="btn btn-dark btn-sm" data-toggle="modal"
                                    data-backdrop="static" data-keyboard="false" data-target="#tareasModal">
                                    <i class="fa-light fa-user-plus"></i> Nueva Tarea
                                </a>
                            </div>
                        </div>

                        <div class="col-auto">
                            <div class="form-group">
                                <a href="javascript:void(0);" class="btn btn-secondary btn-sm" data-toggle="modal"
                                    data-backdrop="static" data-keyboard="false" data-target="#filtroExportarTareasModal">
                                    <i class="fa-solid fa-file-pdf"></i> Exportar Tareas
                                </a>
                            </div>
                        </div>


                        {{-- <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="users_search" class="form-control form-control-round"
                                    placeholder="Buscar tarea....">
                            </div>
                        </div> --}}
                    </div>

                    <div class="row">
                        <!-- Sección "Por Hacer" -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="">Por Hacer</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        @forelse ($todoTasks as $task)
                                            <div class="list-group-item list-group-item-action">
                                                <a href="javascript:void(0);"
                                                    class="text-decoration-none text-dark agregar-comentario"
                                                    id="{{ $task->id }}" data-titulo="{{ $task->titulo }}"
                                                    data-descripcion="{{ $task->descripcion }}"
                                                    data-estado = "{{ $task->estado_id }}">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h5 class="mb-1">{{ $task->titulo }}</h5>
                                                        <small class="text-muted">{{ $task->created_at_formateado }}</small>
                                                    </div>
                                                    <p class="text-truncate-max-line-3 mb-1">{{ $task->descripcion }}</p>

                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger eliminar-tarea"
                                                    id="{{ $task->id }}">Eliminar</button>
                                            </div>
                                        @empty
                                            <small class="text-center">no existen datos para mostrar.</small>
                                        @endforelse
                                    </div>
                                </div>
                            </div>


                        </div>

                        <!-- Sección "En Curso" -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="">En Curso</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        @forelse ($inProgressTasks as $task)
                                            <a href="javascript:void(0);"
                                                class="list-group-item list-group-item-action agregar-comentario"
                                                id="{{ $task->id }}" data-titulo="{{ $task->titulo }}"
                                                data-descripcion="{{ $task->descripcion }}"
                                                data-estado = "{{ $task->estado_id }}">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1">{{ $task->titulo }}</h5>
                                                    <small class="text-muted">{{ $task->created_at_formateado }}</small>
                                                </div>
                                                <p class="text-truncate-max-line-3 mb-1">{{ $task->descripcion }}</p>
                                                <button type="button" href="javascripts:void(0);" id="{{ $task->id }}"
                                                    class="btn btn-sm btn-danger eliminar-tarea">Eliminar</button>
                                            </a>

                                        @empty
                                            <small class="text-center">no existen datos para mostrar.</small>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección "Finalizadas" -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="">Finalizadas</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        @forelse ($completedTasks as $task)
                                            <a href="javascript:void(0);"
                                                class="list-group-item list-group-item-action agregar-comentario"
                                                id="{{ $task->id }}" data-titulo="{{ $task->titulo }}"
                                                data-descripcion="{{ $task->descripcion }}"
                                                data-estado = "{{ $task->estado_id }}">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1">{{ $task->titulo }}</h5>
                                                    <small class="text-muted">{{ $task->created_at_formateado }}</small>
                                                </div>
                                                <p class="text-truncate-max-line-3 mb-1">{{ $task->descripcion }}</p>
                                                <button type="button" href="javascripts:void(0);"
                                                    id="{{ $task->id }}"
                                                    class="btn btn-sm btn-danger eliminar-tarea">Eliminar</button>
                                            </a>
                                        @empty
                                            <small class="text-center">no existen datos para mostrar.</small>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    @include('modals.tareas')
    @include('modals.comentarios_tarea', ['estados' => $estados])
    @include('modals.filtro_exportar_tareas_modal')
@endsection

@section('scripts')
    <script src="{{ asset('js/tareas_scripts.js') }}" type="module"></script>
@endsection
