@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    @include('partials.alerts')
    <div class="card">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <h4 class="mt-2 font-weight-bold">Ordenes de Trabajo</h4>
            </li>
        </ul>
        <div class="card-body ">
            <div class="row">
                <div class="col-md-4 col-12">
                    <div class="form-group">
                        <a href="{{ route('proyecto.adquisiciones.contratista.crear.orden.trabajo',['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id]) }}" class="btn btn-dark btn-sm">
                            <i class="fa-regular fa-plus"></i> Nueva orden de trabajo
                        </a>
                    </div>
                </div>
                <div class="col-md-8 col-12 ">
                    <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                        <i class="fal fa-search fa-lg form-control-icon"></i>
                        <input type="text" name="orden_contratista_search" class="form-control form-control-round"
                            placeholder="Buscar....">
                    </div>
                </div>
            </div>
            <div class="table-responsive" id="table">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Orden Nro.</th>
                            <th scope="col">Contratista</th>
                            <th scope="col">Valor Contrato</th>
                            <th scope="col">Avance</th>
                            <th scope="col">Saldo</th>
                            <th scope="col">Estado</th>
                            <th class="col-accion"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orden_trabajos as $orden_trabajo)
                            <tr id="{{ $orden_trabajo->id }}">
                                <td class="align-middle">{{ $mano_obra->semana }}</td>
                                <td class="align-middle editar-fecha-planificacion" style="cursor: pointer" data-fecha-inicio = "{{ $mano_obra->fecha_inicio }}" data-fecha-fin = "{{ $mano_obra->fecha_fin }}">
                                    {{ dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin) }}</td>
                                <td class="align-middle">{{ $mano_obra->proyecto->nombre_proyecto }}</td>
                                <td class="align-middle">{{ $mano_obra->etapa->descripcion }}</td>
                                <td class="align-middle">{{ $mano_obra->tipo_etapa->descripcion }}</td>
                                <td class="align-middle align-middle text-right text-truncate">
                                    <button type="button" class="btn btn-outline-dark" data-container="body"
                                        data-toggle="popover" data-placement="left" data-trigger="focus"
                                        data-content ="
                                        <a href='{{ route('proyecto.adquisiciones.mano.obra.agregar.trabajadores', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id, 'mano_obra' => $mano_obra->id]) }}' class='dropdown-item' >Agregar Personal</a>
                                        <a href='javascript:void(0);' class='dropdown-item editar-empleados-mano-obra' id='{{ $mano_obra->id }}'>Editar</a>
                                        <a href='#' class='dropdown-item eliminar-planificacion' id='{{ $mano_obra->id }}'>Eliminar</a>
                                        <a href='{{ route('pdf.planificacion.mano.obra', $mano_obra->id) }}' class='dropdown-item'>PDF Planificación</a>">
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
            @include('partials.pagination', ['paginator' => $orden_trabajos, 'interval' => 5])
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var url =
            "{{ route('proyecto.adquisiciones.contratista', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id]) }}";
    </script>
    <script src="{{ asset('js/orden_trabajo_contratistas_scripts.js') }}" type="module"></script>
@endsection

