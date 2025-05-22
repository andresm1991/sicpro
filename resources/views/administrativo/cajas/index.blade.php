@extends('layouts.app')

@section('title', 'Flujo de Caja')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        @include('partials.alerts')
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h4 class="mt-2 font-weight-bold">Flujo de caja</h4>
                </li>
            </ul>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <a href="" class="btn btn-dark btn-sm">
                                <i class="fa-regular fa-plus"></i> Nueva Registro
                            </a>
                        </div>
                    </div>
                    {{--  <div class="col-md-8 col-12 ">
                        <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                            <i class="fal fa-search fa-lg form-control-icon"></i>
                            <input type="text" name="mano_obra_search" class="form-control form-control-round"
                                placeholder="Buscar....">
                        </div>
                    </div>
                    --}}
                </div>
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th scope="col">decripcion</th>
                                <th scope="col">saldo inical</th>
                                <th scope="col">saldo actual</th>
                                <th scope="col">estado</th>
                                <th class="col-accion"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cajas as $caja)
                                <tr id="{{ $mano_obra->id }}">
                                    <td class="align-middle">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        {{ dateFormatHumansManoObra($mano_obra->fecha_desde_formatted, $mano_obra->fecha_hasta_formatted) }}
                                    </td>
                                    <td class="align-middle">{{ $mano_obra->proyecto->nombre_proyecto }}</td>

                                    <td class="align-middle align-middle text-right text-truncate">
                                        <button type="button" class="btn btn-outline-dark" data-container="body"
                                            data-toggle="popover" data-placement="left" data-trigger="focus"
                                            data-content ="
                                            <a href='{{ route('jp.limpieza.mano.obra.edit', [$proyecto->id, $mano_obra->id]) }}' class='dropdown-item'>Editar</a>
                                            <a href='javascript:void(0);' class='dropdown-item eliminar-mano-obra' id='{{ $mano_obra->id }}'>Eliminar</a>
                                            <a href='{{ route('pdf.jp.limpieza.mano.obra', $mano_obra->id) }}' target='_blank' class='dropdown-item'>PDF Planificación</a>">
                                            <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-danger">No se encontraron datos para
                                        mostrar....
                                    </td>
                                </tr>
                            @endforelse


                        </tbody>
                    </table>
                </div>
                {{-- @include('partials.pagination', ['paginator' => $mano_obras, 'interval' => 5]) --}}
            </div>
        </div>
    </section>
@endsection

@section('scripts')

@endsection
