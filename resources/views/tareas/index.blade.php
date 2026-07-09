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
                        @can('agenda.editar')
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
                        @endcan
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

                    <div class="tareas-items">

                        @isset($html)
                            {!! $html !!}
                        @endisset

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
    <script src="{{ asset('js/tareas_scripts.js?v=' . config('app.version', '')) }}" type="module"></script>

    <script>
        var url = "{{ route('tarea.index') }}";
        document.addEventListener('DOMContentLoaded', function() {
            @if (isset($modalToShow) && !empty($modalToShow['activeModal']) && isset($modalToShow['tarea']) && $modalToShow['tarea'])
                setTimeout(() => {
                    const taskId = {{ $modalToShow['tarea']->id ?? 'null' }};
                    if (taskId) {
                        const element = $(`#${taskId}.agregar-comentario`);
                        if (element.length) {
                            element.trigger('click');
                        }
                    }
                }, 100);
            @endif
        });
    </script>
@endsection
