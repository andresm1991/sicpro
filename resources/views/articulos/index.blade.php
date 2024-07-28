@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Productos registrados</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="javascript:void(0);" class="btn btn-dark btn-sm" data-toggle="modal"
                                    data-backdrop="static" data-keyboard="false" data-target="#articuloFormModal">
                                    <i class="fa-solid fa-plus-large"></i> Nuevo Producto
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="articulo_search" class="form-control form-control-round"
                                    placeholder="Buscar producto....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Categoría</th>
                                    <th scope="col">Código</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Estado</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($articulos as $articulo)
                                    <tr id="{{ $articulo->id }}">
                                        <td class="align-middle">{{ $articulo->categoria_articulo->descripcion }}</td>
                                        <td class="align-middle text-uppercase">{{ $articulo->codigo }}</td>
                                        <td class="align-middle">{{ $articulo->descripcion }}</td>
                                        <td class="align-middle">
                                            @if ($articulo->activo)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="align-middle table-actions">
                                            <div class="action-buttons">
                                                <a href="javascript:void(0);"
                                                    class="btn btn-secondary btn-sm btn-space editar_articulo"
                                                    data-id="{{ $articulo->id }}"
                                                    data-categoria="{{ $articulo->categoria_id }}"
                                                    data-codigo="{{ $articulo->codigo }}"
                                                    data-descripcion="{{ $articulo->descripcion }}"
                                                    data-estado="{{ $articulo->activo }}"><i
                                                        class="fa-light fa-pen-to-square"></i> Editar</a>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm delete-articulo"
                                                    id="{{ $articulo->id }}"><i class="fa-solid fa-trash-can"></i>
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

                    @include('partials.pagination', ['paginator' => $articulos, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

    @include('modals.articulo_form_modal')
@endsection

@section('scripts')
    <script src="{{ asset('js/articulo_scripts.js') }}" type="module"></script>
@endsection
