@extends('layouts.app')

@section('title', 'Editar Rol - ' . $role->name)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h5 class="mt-2">Editar permisos: <strong>{{ $role->name }}</strong></h5>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    <form action="{{ route('sistema.roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @foreach ($modulePermissions as $module)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0 font-weight-bold">{{ $module['parent']->descripcion ?? $module['parent']->name }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($module['children'] as $permission)
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permission->id }}"
                                                           id="perm_{{ $permission->id }}"
                                                           class="form-check-input"
                                                           @if(in_array($permission->id, $rolePermissions)) checked @endif>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ $permission->descripcion ?? $permission->name }}
                                                        <small class="text-muted">({{ $permission->name }})</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-12 text-center mt-3">
                            <a href="{{ route('sistema.roles.index') }}" class="btn btn-secondary btn-options mr-2">
                                <i class="fa-solid fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-dark btn-options">
                                <i class="fa-solid fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>

                    <div class="col-12 mt-4">
                        <p class="font-italic">Selecciona los permisos que deseas asignar a este rol. Los cambios se aplican inmediatamente.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
