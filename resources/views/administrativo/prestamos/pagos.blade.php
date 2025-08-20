@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        @include('partials.alerts')
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h4 class="mt-2 font-weight-bold">Pagos</h4>
                </li>
            </ul>
            <div class="card-body ">
                <legend class="custom-legend"><span>Información General</span></legend>
                <fieldset class="custom-fieldset">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="col-form-label">Trabajador</label>
                                <input type="text" class="form-control text-uppercase" readonly
                                    value="{{ $prestamo->trabajador->razon_social }}">
                            </div>

                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Estado</label>
                                <input type="text" class="form-control text-uppercase" readonly
                                    value="{{ $prestamo->estado->descripcion }}">
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="col-form-label">Fecha vencimiento</label>
                                <input type="text" class="form-control text-uppercase" readonly
                                    value="{{ dateFormatHumans($prestamo->fecha_vencimiento) }}">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Monto</label>
                                <input type="text" class="form-control" readonly
                                    value="$ {{ number_format($prestamo->monto, 2) }}">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Saldo</label>
                                <input type="text" class="form-control" readonly
                                    value="$ {{ number_format($prestamo->saldo, 2) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-dark recalcular-pagos-prestamo" id="{{ $prestamo->id }}">Recalcular
                                Pagos</button>
                        </div>
                    </div>
                </fieldset>
                <div class="table-responsive" id="tabla-pagos">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Fecha pago</th>
                                <th scope="col">Monto programado</th>
                                <th scope="col">Monto pagado</th>
                                <th scope="col">Forma de pago</th>
                                <th scope="col">Estado</th>
                                <th class="col-accion"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pagos as $pago)
                                <tr id="{{ $pago->id }}">
                                    <td class="align-middle">{{ $pago->fecha_pago }}</td>
                                    <td class="align-middle">$ {{ number_format($pago->monto_programado, 2) }}</td>
                                    <td class="align-middle">$ {{ number_format($pago->monto_pagado, 2) }}</td>
                                    <td class="align-middle">{{ $pago->metodo_pago->descripcion }}</td>
                                    <td class="align-middle"> <span
                                            class="badge badge-{{ $pago->estado->slug == 'estados.pagos.prestamos.pagado' ? 'success' : ($pago->estado->slug == 'estados.pagos.prestamos.pendiente' ? 'warning' : 'danger') }}">{{ $pago->estado->descripcion }}</span>
                                    </td>
                                    <td class="align-middle ">
                                        <div class="btn-group  dropleft">
                                            <button type="button" class="btn btn-outline-dark dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Opciones
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item registrar-pago" href="javascript:void(0)"
                                                    data-monto-programado="{{ $pago->monto_programado }}"
                                                    data-estado="{{ $pago->estado->descripcion }}"
                                                    data-pago="{{ $pago->id }}">Registrar Pago</a>
                                                <a class="dropdown-item posponer-pago" data-pago="{{ $pago->id }}"
                                                    href="javascript:void(0)">Posponer
                                                    Pago</a>
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
                @include('partials.pagination', ['paginator' => $pagos, 'interval' => 5])
            </div>
        </div>
        @include('modals.pago_modal')

    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/prestamos_scripts.js') }}" type="module"></script>
@endsection
