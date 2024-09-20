@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <div class="card">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <h4 class="mt-2 font-weight-bold">Mano de Obra</h4>
            </li>
        </ul>
        <div class="card-body ">
            <div class="row">
                <div class="col-md-4 col-12">
                    <div class="form-group">
                        <a href="{{ route('proyecto.adquisiciones.mano.obra.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion->id, 'tipo_etapa' => $tipo_etapa->id]) }}"
                            class="btn btn-dark btn-sm">
                            <i class="fa-regular fa-plus"></i> Nueva Planificación
                        </a>
                    </div>
                </div>
                <div class="col-md-8 col-12 ">
                    <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                        <i class="fal fa-search fa-lg form-control-icon"></i>
                        <input type="text" name="mano_obra_search" class="form-control form-control-round"
                            placeholder="Buscar....">
                    </div>
                </div>
            </div>
            <div class="table-responsive" id="table">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Proyecto</th>
                            <th scope="col">Etapa</th>
                            <th scope="col">Tipo</th>
                            <th class="col-accion"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list_mano_obra as $mano_obra)
                            <tr id="{{ $mano_obra->id }}">
                                <td class="align-middle">{{ $mano_obra->id }}</td>
                                <td class="align-middle">{{ date('d-m-Y', strtotime($mano_obra->fecha)) }}</td>
                                <td class="align-middle">{{ $mano_obra->proyecto->nombre_proyecto }}</td>
                                <td class="align-middle">{{ $mano_obra->etapa->descripcion }}</td>
                                <td class="align-middle">{{ $mano_obra->tipo_etapa->descripcion }}</td>
                                <td class="align-middle align-middle text-right text-truncate">
                                    <button type="button" class="btn btn-outline-dark" data-container="body"
                                        data-toggle="popover" data-placement="left" data-trigger="focus"
                                        data-content ="
                                        <a href='{{ route('proyecto.adquisiciones.orden.pedido.edit', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id, 'pedido' => $mano_obra->id]) }}' class='dropdown-item'>Editar</a>
                                        <a href='#' class='dropdown-item eliminar-pedido' id='{{ $mano_obra->id }}'>Eliminar</a>
                                        <a href='{{ route('pdf.adquisicion', $mano_obra->id) }}' class='dropdown-item'>PDF</a>">
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
            @include('partials.pagination', ['paginator' => $list_mano_obra, 'interval' => 5])
        </div>
    </div>
@endsection
