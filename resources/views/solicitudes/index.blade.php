@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Solicitudes registradas</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ route('solicitud.create') }}"
                                    class="btn btn-dark btn-sm">
                                    <i class="fa-light fa-user-plus"></i> Nueva Solicitud
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="solicitud_search" class="form-control form-control-round"
                                    placeholder="Buscar solicitud....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Colaborador</th>
                                    <th scope="col">Fecha Solicitud</th>
                                    <th scope="col">Tipo Solicitud </th>
                                    <th scope="col">Recuperable</th>
                                    <th scope="col">Estado</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($solicitudes as $solicitud)
                                    <tr id="{{ $solicitud->id }}">
                                        <td class="align-middle text-capitalize">{{ $solicitud->id }}</td>
                                        <td class="align-middle text-capitalize">{{ $solicitud->usuario->nombre }}</td>
                                        <td class="align-middle">{{ dateFormatHumans($solicitud->fecha_solicitud) }}</td>
                                        <td class="align-middle">{{ $solicitud->tipo_solicitud->descripcion }}</td>
                                        <td class="align-middle">{{ $solicitud->recuperable ? 'SI' : 'NO' }}</td>
                                        <td class="align-middle">{{ $solicitud->estado_solicitud->descripcion }}</td>
                                        <td class="align-middle">
                                            <div class="btn-group dropleft">
                                                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  Opciones
                                                </button>
                                                <div class="dropdown-menu">
                                                  <!-- Dropdown menu links -->
                                                  <a href='' class='dropdown-item {{ $solicitud->recuperable ? '':'disabled' }}'>Resposición</a>
                                                <a href='{{ route('solicitud.edit', $solicitud->id) }}' class='dropdown-item'>Editar</a>
                                                <a href='javascript:void(0);' class='dropdown-item eliminar-solicitud' id='{{ $solicitud->id }}'>Eliminar</a>
                                                </div>
                                              </div>
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

                    @include('partials.pagination', ['paginator' => $solicitudes, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
<script src="{{ asset('js/solicitud_scripts.js') }}"></script>
@endsection
