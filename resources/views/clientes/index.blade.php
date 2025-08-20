@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')

    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            @include('partials.alerts')
            <div class="card">
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ route('sistema.clientes.create') }}" class="btn btn-dark btn-sm">
                                    <i class="fa-light fa-plus"></i> Nuevo cliente
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="clientes_search" class="form-control form-control-round"
                                    placeholder="ingrese identificación o cliente para Buscar...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover  table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">identificación</th>
                                    <th scope="col">Teléfono</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col" class="text-center">Activo</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clientes as $cliente)
                                    <tr id="{{ $cliente->id }}">
                                        <td class="align-middle">{{ $cliente->nombre }}</td>
                                        <td class="align-middle">{{ $cliente->ruc }}</td>
                                        <td class="align-middle">{{ $cliente->telefono }}</td>
                                        <td class="align-middle">{{ $cliente->correo }}</td>
                                        <td class="align-middle text-center">
                                            <span
                                                class="badge {{ $cliente->activo ? 'badge-success' : 'badge-warning' }} ">{{ $cliente->activo ? 'SI' : 'NO' }}</span>
                                        </td>
                                        <td class="align-middle table-actions">
                                            <div class="action-buttons">
                                                <a href="{{ route('sistema.clientes.edit', $cliente->id) }}"
                                                    class="btn btn-secondary btn-sm btn-space"><i
                                                        class="fa-light fa-pen-to-square"></i> Editar</a>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-cliente"
                                                    id="{{ $cliente->id }}"><i class="fa-solid fa-trash-can"></i>
                                                    Eliminar</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>

                    @include('partials.pagination', ['paginator' => $clientes, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script src="{{ asset('js/clientes.js?v=' . config('app.version', '')) }}"></script>
@endsection
