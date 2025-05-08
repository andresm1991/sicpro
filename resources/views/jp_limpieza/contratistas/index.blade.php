@extends('layouts.app')

@section('title', 'Contratistas')

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
                                <a href="{{ route('jp.limpieza.contratistas.create', $proyecto) }}"
                                    class="btn btn-dark btn-sm">
                                    <i class="fa-regular fa-plus"></i> Nuevo Contratista
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="buscar-adquisicion" id="buscar-adquisicion"
                                    class="form-control form-control-round" placeholder="Buscar....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table">
                        <table id="table-list-pedidos" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Orden Nro.</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Proveedor</th>
                                    <th scope="col">Categoria</th>
                                    <th scope="col">Plazo</th>
                                    <th scope="col">Total Contratado</th>
                                    <th scope="col">Total pagado</th>
                                    <th scope="col">Total Pendiente</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contratistas as $contratista)
                                    <tr id="{{ $contratista->id }}">
                                        <td class="align-middle">{{ $contratista->numero }}</td>
                                        <td class="align-middle">{{ date('d-m-Y', strtotime($contratista->fecha)) }}</td>
                                        <td class="align-middle">{{ $contratista->proveedor->razon_social }}</td>
                                        <td class="align-middle">{{ $contratista->categoria->descripcion }}</td>
                                        <td class="align-middle">{{ $contratista->plazo }}</td>
                                        <td class="align-middle">$ {{ $contratista->total_contratado_formatted }}</td>
                                        <td class="align-middle">$ {{ $contratista->total_pagado_formatted }}</td>
                                        <td class="align-middle">$ {{ $contratista->total_pendiente_formatted }}</td>
                                        <td class="align-middle text-right text-truncate">
                                            <button type="button" class="btn btn-outline-dark" data-container="body"
                                                data-toggle="popover" data-placement="left" data-trigger="focus"
                                                data-content ="
                                                <a href='{{ route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista->id]) }}' class='dropdown-item'>Pagos</a>
                                                <a href='' class='dropdown-item'>Editar</a>
                                                <a href='#' class='dropdown-item eliminar' id='{{ $contratista->id }}'>Eliminar</a>
                                                <a href='' class='dropdown-item' target='_blank'>Generar PDF</a> ">
                                                <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>
                    @include('partials.pagination', ['paginator' => $contratistas, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')

@endsection
