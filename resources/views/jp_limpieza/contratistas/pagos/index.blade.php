@extends('layouts.app')

@section('title', 'Pagos Contratista')

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
                                <a href="{{ route('jp.limpieza.contratistas.pagos.create', [$proyecto, $contratista]) }}"
                                    class="btn btn-dark btn-sm">
                                    <i class="fa-regular fa-plus"></i> Nuevo Pago
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="buscar-pagos" id="buscar-pagos"
                                    class="form-control form-control-round" placeholder="Buscar....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Pago Nro.</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Monto</th>
                                    <th scope="col">Tipo Pago</th>
                                    <th scope="col">Forma Pago</th>
                                    <th scope="col">Estado</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pagos as $pago)
                                    <tr id="{{ $pago->id }}">
                                        <td class="align-middle">{{ $pago->numero_formatted }}</td>
                                        <td class="align-middle">{{ $pago->fecha_formatted }}</td>
                                        <td class="align-middle">{{ $pago->monto }}</td>
                                        <td class="align-middle">{{ $pago->tipo_pago }}</td>
                                        <td class="align-middle">{{ $pago->formaPago->descripcion }}</td>
                                        <td class="align-middle">{{ $pago->estado->descripcion }}</td>
                                        <td class="align-middle text-right text-truncate">
                                            <button type="button" class="btn btn-outline-dark" data-container="body"
                                                data-toggle="popover" data-placement="left" data-trigger="focus"
                                                data-content ="
                                                <a href='{{ route('jp.limpieza.contratistas.pagos.edit', [$proyecto, $contratista, $pago->id]) }}' class='dropdown-item'>Editar</a>
                                                <a href='#' class='dropdown-item eliminar' id=''>Eliminar</a> ">
                                                <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-danger">No se encontraron datos para
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
        </div>
    </section>
@endsection

@section('scripts')

@endsection
