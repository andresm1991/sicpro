@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')

    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Reposiciones de tiempo</h4>
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
                                <input type="text" name="reposicion_search" class="form-control form-control-round"
                                    placeholder="Buscar colaborador....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Colaborador</th>
                                    <th scope="col">Tiempo Acumulado</th>
                                    <th scope="col">Tiempo Recuperado</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($solicitudeUsuarios as $index => $recuperacion)
                                    <tr id="{{ $index }}">
                                        <td class="align-middle">{{ $index + 1 }}</td>
                                        <td class="align-middle text-capitalize">{{ $recuperacion->usuario->nombre }}</td>
                                        <td class="align-middle">{{ $recuperacion->tiempo_acumulado_formateado }}</td>
                                        <td class="align-middle">{{ $recuperacion->timpo_recuperado_formateada }}</td>
                                        <td class="align-middle text-right text-truncate">
                                            <a href="{{ route('solicitud.reposicion.detalle', $recuperacion->usuario_id) }}"
                                                class="btn btn-outline-dark">Ver Detalle
                                            </a>
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

                    @include('partials.pagination', ['paginator' => $solicitudeUsuarios, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script src="{{ asset('js/reposicion_tiempo_scripts.js') }}"></script>
@endsection
