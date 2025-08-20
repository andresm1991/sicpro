@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')

    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Solicitudes Reposiciones de tiempo</h4>
                        <h6>{{ $usuario->nombre }}</h6>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ route('solicitud.reposicion.create') }}" class="btn btn-dark btn-sm">
                                    <i class="fa-light fa-plus"></i> Nueva Reposición
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="solicitud_reposicion_search"
                                    data-usuario-id="{{ $usuario->id }}" class="form-control form-control-round"
                                    placeholder="Buscar colaborador....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Hora inicio</th>
                                    <th scope="col">Hora fin</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">estado</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($solicitudesReposicion as $index => $recuperacion)
                                    <tr id="{{ $recuperacion->id }}">
                                        <td class="align-middle">{{ $index + 1 }}</td>
                                        <td class="align-middle">{{ $recuperacion->fecha }}</td>
                                        <td class="align-middle">{{ $recuperacion->hora_desde }}</td>
                                        <td class="align-middle">{{ $recuperacion->hora_hasta }}</td>
                                        <td class="align-middle">{{ $recuperacion->total }}</td>
                                        <td class="align-middle">{{ $recuperacion->estado->descripcion }}</td>
                                        <td class="align-middle align-middle text-right text-truncate">
                                            <button type="button" class="btn btn-outline-dark" data-container="body"
                                                data-toggle="popover" data-placement="left" data-trigger="focus"
                                                data-content="
                                                <a href='{{ route('solicitud.reposicion.edit', $recuperacion->id) }}' class='dropdown-item'>Editar</a>
                                                    <a href='#' class='dropdown-item eliminar-solicitid-reposicion' id='{{ $recuperacion->id }}'>Eliminar</a> ">
                                                <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>

                    @include('partials.pagination', [
                        'paginator' => $solicitudesReposicion,
                        'interval' => 5,
                    ])
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script src="{{ asset('js/reposicion_tiempo_scripts.js') }}"></script>
@endsection
