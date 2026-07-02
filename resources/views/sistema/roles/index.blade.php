@extends('layouts.app')

@section('title', 'Roles y Permisos')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Roles y Permisos del sistema</h4>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Rol</th>
                                    <th scope="col"># Permisos</th>
                                    <th scope="col"># Usuarios</th>
                                    <th class="table-actions">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td class="align-middle">
                                            <strong>{{ $role->name }}</strong>
                                            <span class="badge badge-primary ml-2">{{ $role->permissions_count }}</span>
                                        </td>
                                        <td class="align-middle text-center">{{ $role->permissions_count }}</td>
                                        <td class="align-middle text-center">{{ $role->users_count }}</td>
                                        <td class="align-middle table-actions">
                                            <div class="action-buttons">
                                                <a href="{{ route('sistema.roles.edit', $role) }}"
                                                    class="btn btn-secondary btn-sm btn-space">
                                                    <i class="fa-light fa-pen-to-square"></i> Editar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-danger">
                                            No se encontraron datos para mostrar....
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
