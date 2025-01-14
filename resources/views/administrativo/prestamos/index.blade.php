@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        @include('partials.alerts')
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h4 class="mt-2 font-weight-bold">Prestamos</h4>
                </li>
            </ul>
            <div class="card-body ">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <a class="btn btn-dark btn-sm" data-toggle="modal" data-backdrop="static" data-keyboard="false"
                                data-target="#modalPrestamo">
                                <i class="fa-regular fa-plus"></i> Nuevo Prestamo
                            </a>
                        </div>
                    </div>
                    <div class="col-md-8 col-12 ">
                        <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                            <i class="fal fa-search fa-lg form-control-icon"></i>
                            <input type="text" name="search_prestamo" class="form-control form-control-round"
                                placeholder="Buscar....">
                        </div>
                    </div>
                </div>
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Trabajador</th>
                                <th scope="col">Monto</th>
                                <th scope="col">Plazo</th>
                                <th scope="col">Saldo</th>
                                <th scope="col">Fecha Vencimiento</th>
                                <th scope="col">Estado</th>
                                <th class="col-accion"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prestamos as $prestamo)
                                <tr id="{{ $prestamo->id }}">
                                    <td class="align-middle">{{ strtoupper($prestamo->trabajador->razon_social) }}</td>
                                    <td class="align-middle">$ {{ number_format($prestamo->monto, 2) }}</td>
                                    <td class="align-middle">{{ $prestamo->plazo }} semanas</td>
                                    <td class="align-middle">$ {{ number_format($prestamo->saldo, 2) }}</td>
                                    <td class="align-middle"> {{ dateFormatHumans($prestamo->fecha_vencimiento) }}</td>
                                    <td class="align-middle"> <span
                                            class="badge badge-{{ $prestamo->estado->descripcion == 'Pagado' ? 'success' : 'warning' }}">{{ $prestamo->estado->descripcion }}</span>
                                    </td>
                                    <td class="align-middle ">
                                        <div class="btn-group  dropleft">
                                            <button type="button" class="btn btn-outline-dark dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Opciones
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href='{{ route('administrativo.prestamos.detalle.prestamo', $prestamo->id) }}'
                                                    class="dropdown-item">Detalle</a>
                                                <a class="dropdown-item editar" data-prestamo="{{ $prestamo->id }}"
                                                    data-monto="{{ $prestamo->monto }}"
                                                    data-plazo="{{ $prestamo->plazo }}"
                                                    data-saldo="{{ $prestamo->saldo }}"
                                                    data-interes="{{ $prestamo->interes }}"
                                                    data-motivo="{{ $prestamo->motivo }}"
                                                    data-estado="{{ $prestamo->estado_id }}"
                                                    data-trabajador="{{ $prestamo->trabajador_id }}"
                                                    data-fecha-solicitud="{{ $prestamo->fecha_solicitud }}"
                                                    data-fecha-aprobacion="{{ $prestamo->fecha_aprobacion }}"
                                                    data-fecha-vencimiento="{{ $prestamo->fecha_vencimiento }}"
                                                    href="javascript:void(0)">Editar</a>
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
                @include('partials.pagination', ['paginator' => $prestamos, 'interval' => 5])
            </div>
        </div>

        @include('modals.prestamo_modal')
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/prestamos_scripts.js') }}" type="module"></script>
@endsection
