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
                            <a href="javascript:void(0);" class="text-decoration-none text-dark agregar-comentario"
                                id="{{ $task->id }}" data-titulo="{{ $task->titulo }}"
                                data-descripcion="{{ $task->descripcion }}" data-estado = "{{ $task->estado_id }}"
                                data-colaboradores="{{ $task->usuario_tareas->pluck('id') }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $task->titulo }}</h5>
                                    <small class="text-muted">{{ $task->created_at_formateado }}</small>
                                </div>
                                <p class="text-truncate-max-line-3 mb-1">{{ $task->descripcion }}</p>

                            </a>
                            <p class="card-text"><small class="text-primary">Creado por:
                                    {{ $task->usuario->nombre }} </small></p>
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
                        <div class="list-group-item list-group-item-action">
                            <a href="javascript:void(0);" class="text-decoration-none text-dark agregar-comentario"
                                id="{{ $task->id }}" data-titulo="{{ $task->titulo }}"
                                data-descripcion="{{ $task->descripcion }}" data-estado = "{{ $task->estado_id }}"
                                data-colaboradores="{{ $task->usuario_tareas->pluck('id') }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $task->titulo }}</h5>
                                    <small class="text-muted">{{ $task->created_at_formateado }}</small>
                                </div>
                                <p class="text-truncate-max-line-3 mb-1">{{ $task->descripcion }}</p>
                            </a>
                            <p class="card-text"><small class="text-primary">Creado por:
                                    {{ $task->usuario->nombre }} </small></p>
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

    <!-- Sección "Finalizadas" -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="">Finalizadas</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @forelse ($completedTasks as $task)
                        <div class="list-group-item list-group-item-action">
                            <a href="javascript:void(0);" class="text-decoration-none text-dark agregar-comentario"
                                id="{{ $task->id }}" data-titulo="{{ $task->titulo }}"
                                data-descripcion="{{ $task->descripcion }}" data-estado = "{{ $task->estado_id }}"
                                data-colaboradores="{{ $task->usuario_tareas->pluck('id') }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $task->titulo }}</h5>
                                    <small class="text-muted">{{ $task->created_at_formateado }}</small>
                                </div>
                                <p class="text-truncate-max-line-3 mb-1">{{ $task->descripcion }}</p>
                            </a>
                            <p class="card-text"><small class="text-primary">Creado por:
                                    {{ $task->usuario->nombre }} </small></p>
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
</div>
