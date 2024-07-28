@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Usuario del sistema</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ route('sistema.users.create') }}" class="btn btn-dark btn-sm">
                                    <i class="fa-light fa-user-plus"></i> Nuevo Usuario
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="users_search" class="form-control form-control-round"
                                    placeholder="Buscar usuario....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Usuario</th>
                                    <th scope="col">Perfil</th>
                                    <th scope="col">Estado</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr id="{{ $user->id }}">
                                        <td class="align-middle">{{ $user->id }}</td>
                                        <td class="align-middle text-capitalize">{{ $user->nombre }}</td>
                                        <td class="align-middle">{{ $user->usuario }}</td>
                                        <td class="align-middle">{{ $user->roles()->first()->name }}</td>
                                        <td class="align-middle">
                                            @if ($user->activo)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="align-middle table-actions">
                                            <div class="action-buttons">
                                                <a href="{{ route('sistema.users.edit', $user->id) }}"
                                                    class="btn btn-secondary btn-sm btn-space"><i
                                                        class="fa-light fa-pen-to-square"></i> Editar</a>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm delete-user"
                                                    id="{{ $user->id }}"><i class="fa-solid fa-trash-can"></i>
                                                    Eliminar</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>

                    @include('partials.pagination', ['paginator' => $users, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script src="{{ asset('js/user_scripts.js') }}"></script>
@endsection
