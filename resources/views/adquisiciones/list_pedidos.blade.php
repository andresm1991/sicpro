@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="mi">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Adquisiciones</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ route('proyecto.adquisiciones.tipo.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion->id, 'tipo_etapa' => $tipo_etapa->id]) }}"
                                    class="btn btn-dark btn-sm">
                                    <i class="fa-regular fa-plus"></i> Nuevo Pedido
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="adquisicion_search" class="form-control form-control-round"
                                    placeholder="Buscar....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Fecha Pedido</th>
                                    <th scope="col">Proyecto</th>
                                    <th scope="col">Etapa</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Estado</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list_pedidos as $pedido)
                                    <tr id="{{ $pedido->id }}">
                                        <td class="align-middle">{{ $pedido->numero }}</td>
                                        <td class="align-middle">{{ date('d-m-Y', strtotime($pedido->fecha)) }}</td>
                                        <td class="align-middle">{{ $pedido->proyecto->nombre_proyecto }}</td>
                                        <td class="align-middle">{{ $pedido->etapa->descripcion }}</td>
                                        <td class="align-middle">{{ $pedido->tipo_etapa->descripcion }}</td>
                                        <td class="align-middle">
                                            <span
                                                class="badge {{ $pedido->estado == 'Finalizado' ? 'badge-success' : 'badge-warning' }} ">{{ $pedido->estado }}</span>
                                        </td>
                                        <td class="align-middle align-middle text-right text-truncate">
                                            <button type="button" class="btn btn-outline-dark" data-container="body"
                                                data-toggle="popover" data-placement="left" data-trigger="focus"
                                                data-content ="
                                                <a href='#' class='dropdown-item'>Editar</a>
                                                <a href='#' class='dropdown-item'>Eliminar</a>
                                                <a href='{{ route('pdf.adquisicion', $pedido->id) }}' class='dropdown-item'>PDF</a>
                                                <a href='{{ route('proyecto.adquisiciones.orden.recepcion', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id, 'pedido' => $pedido->id]) }}' class='dropdown-item'>Orden de Recepción</a>">
                                                <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>

                    @include('partials.pagination', ['paginator' => $list_pedidos, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>
@endsection
