@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="card">
            <div class="card-body ">
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Fecha</th>
                                <th scope="col">Producto</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Estado</th>
                                <th class="col-accion"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($detalle_inventario as $index => $inventario)
                                <tr id="{{ $index }}">
                                    <td class="align-middle">{{ $inventario->fecha }}</td>
                                    <td class="align-middle text-uppercase">{{ $inventario->producto->descripcion }}</td>
                                    <td class="align-middle">{{ $inventario->cantidad }}</td>
                                    <td class="align-middle">
                                        <div class="progress">
                                            <div class="progress-bar {{ $inventario->estado <= 3 ? 'bg-danger' : ($inventario->estado <= 7 ? 'bg-warning' : 'bg-success') }} "
                                                role="progressbar" style="width: {{ ($inventario->estado / 10) * 100 }}%;"
                                                aria-valuenow="{{ $inventario->estado }}" aria-valuemin="0"
                                                aria-valuemax="10">
                                                {{ $inventario->estado }}/10
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-right text-truncate p-2">
                                        <button type="button" class="btn btn-outline-dark" data-container="body"
                                            data-toggle="popover" data-placement="left" data-trigger="focus"
                                            data-content ="
                                            <a href='javascript:void(0);' class='dropdown-item dar_baja' id = '{{ $inventario->id }}' data-cantidad ='{{ $inventario->cantidad }}' >Dar de Baja</a>
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

                @include('partials.pagination', ['paginator' => $detalle_inventario, 'interval' => 5])
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script src="{{ asset('js/inventario_scripts.js') }}"></script>
@endsection
