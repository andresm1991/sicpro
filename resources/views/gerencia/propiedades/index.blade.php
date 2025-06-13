@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="mi">
            @include('partials.alerts')
            <div class="card">
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ route('gerencia.propiedades.create') }}" class="btn btn-dark btn-sm">
                                    <i class="fa-regular fa-plus"></i> Nueva propiedad
                                </a>
                                <a href="{{ route('gerencia.propiedades.mapa') }}" class="btn btn-dark btn-sm">
                                    <i class="fa-solid fa-map-location-dot"></i> ver mapa
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" id="buscar-propiedad" class="form-control form-control-round"
                                    placeholder="Buscar....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table">
                        <table id="table-list-propiedades" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha registro</th>
                                    <th scope="col">propiedad</th>
                                    <th scope="col">area</th>
                                    <th scope="col">precio venta</th>
                                    <th scope="col">telefono</th>
                                    <th scope="col">Estado</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($propiedades as $propiedad)
                                    <tr id="{{ $propiedad->id }}">
                                        <td class="align-middle">{{ $propiedad->created_at_formatted }}</td>
                                        <td class="align-middle">{{ $propiedad->nombre }}</td>
                                        <td class="align-middle">{{ $propiedad->area_formatted }}</td>
                                        <td class="align-middle">$ {{ $propiedad->precio_venta_formatted }}</td>
                                        <td class="align-middle">{{ $propiedad->telefono }}</td>
                                        <td class="align-middle">
                                            <span
                                                class="badge {{ $propiedad->estado == 'VENDIDO' ? 'badge-warning' : 'badge-success' }} ">{{ $propiedad->estado }}</span>
                                        </td>
                                        <td class="align-middle text-right">
                                            <div class="btn-group dropleft">
                                                <button type="button" class="btn btn-outline-dark dropdown-toggle"
                                                    data-container="body" data-toggle="dropdown" aria-expanded="false">
                                                    Opciones
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href='javascript:void(0);' class='dropdown-item compartir-ubicacion'
                                                        data-ubicacion='{{ json_encode(['lat' => $propiedad->latitud, 'lng' => $propiedad->longitud]) }}'
                                                        data-nombre='{{ $propiedad->nombre }}'>Compartir ubicacón</a>
                                                    <a href='{{ route('gerencia.propiedades.edit', $propiedad->id) }}'
                                                        class='dropdown-item'>Detalle</a>
                                                    <a href='#' class='dropdown-item eliminar-propiedad'
                                                        id='{{ $propiedad->id }}'>Eliminar</a>
                                                </div>
                                            </div>
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
                    @include('partials.pagination', ['paginator' => $propiedades, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/propiedades.js?v=' . config('app.version', '')) }}"></script>
@endsection
