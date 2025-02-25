@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Pagos registrados</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="javascript:void(0);" class="btn btn-dark btn-sm" data-toggle="modal"
                                    data-backdrop="static" data-keyboard="false" data-target="#pagoSemanalModal">
                                    <i class="fa-light fa-plus"></i> Nuevo Registro
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="users_search" class="form-control form-control-round"
                                    placeholder="Buscar pago....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="resumen_pagos_table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Estado</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($resumen_pagos as $resumen)
                                    <tr id="{{ $resumen->id }}">
                                        <th class="align-middle">{{ $resumen->id }}</th>
                                        <td class="align-middle">{{ dateFormat('Y-m-d', 'd-m-Y', $resumen->fecha) }}</td>
                                        <td class="align-middle">$ {{ number_format($resumen->total, 4) }}</td>
                                        <td class="align-middle">
                                            @if ($resumen->estado->slug == 'estados.resumen.pagos.semanales.pendiente')
                                                <span class="badge badge-warning">{{ $resumen->estado->descripcion }}</span>
                                            @elseif ($resumen->estado->slug == 'estados.resumen.pagos.semanales.aprobado')
                                                <span class="badge badge-success">{{ $resumen->estado->descripcion }}</span>
                                            @elseif ($resumen->estado->slug == 'estados.resumen.pagos.semanales.cancelado')
                                                <span class="badge badge-danger">{{ $resumen->estado->descripcion }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle table-actions">
                                            <a href="javascript:void(0);" class="btn btn-dark btn-sm editar-resumen"
                                                data-toggle="modal" data-backdrop="static" data-keyboard="false"
                                                data-target="#pagoSemanalModal" id="{{ $resumen->id }}">
                                                <i class="fa-light fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-dark btn-sm"
                                                id="{{ $resumen->id }}">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-dark btn-sm eliminar-resumen"
                                                id="{{ $resumen->id }}">
                                                <i class="fa-light fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>

                    @include('partials.pagination', ['paginator' => $resumen_pagos, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

    @include('modals.resumen_pagos_semanales')
@endsection

@section('scripts')
    <script src="{{ asset('js/resumen_pagos_semanales_scripts.js') }}" type="module"></script>
@endsection
