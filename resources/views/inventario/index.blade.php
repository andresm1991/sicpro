@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="card">
            <div class="card-body ">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <a href="javascript:void(0);" class="btn btn-dark btn-sm" data-toggle="modal"
                                data-backdrop="static" data-keyboard="false" data-target="#inventarioFormModal">
                                <i class="fa-regular fa-plus"></i> Ingresar producto
                            </a>
                        </div>
                    </div>
                    <div class="col-md-8 col-12 ">
                        <div class="form-group form-search form-icon col-md-10 col-12 p-0">
                            <i class="fal fa-search fa-lg form-control-icon"></i>
                            <input type="text" name="inventario_search" class="form-control form-control-round"
                                placeholder="Buscar....">
                        </div>
                    </div>
                </div>

                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Producto</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">De Baja</th>
                                <th scope="col">Stock</th>
                                <th scope="col">Estado</th>
                                <th class="col-accion"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($list_inventario as $index => $inventario)
                                <tr id="{{ $index }}">
                                    <td class="align-middle text-uppercase">{{ $inventario->producto->descripcion }}</td>
                                    <td class="align-middle">{{ $inventario->total_cantidad }}</td>
                                    <td class="align-middle">{{ $inventario->total_cantidad_debaja }}</td>
                                    <td class="align-middle">{{ $inventario->stock }}</td>
                                    <td class="align-middle">{{ $inventario->estado->descripcion }}</td>
                                    <td class="align-middle text-right text-truncate p-2">
                                        <button type="button" class="btn btn-outline-dark" data-container="body"
                                            data-toggle="popover" data-placement="left" data-trigger="focus"
                                            data-content ="
                                            <a href='javascript:void(0);' class='dropdown-item'>Movimiento</a>
                                            <a href='javascript:void(0);' class='dropdown-item eliminar-inventario' id='{{ $inventario->id }}'>Eliminar</a>
                                        ">
                                            <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No éxisten datos para mostrar...</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    @include('modals.inventario_form_modal', ['productos' => $productos, 'estados' => $estados])
@endsection

@section('scripts')
    <script src="{{ asset('js/inventario_scripts.js') }}"></script>
@endsection
