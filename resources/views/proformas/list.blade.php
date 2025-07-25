@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')

    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            @include('partials.alerts')
            <div class="card">
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <a href="{{ route('proformas.create', $tipo) }}" class="btn btn-dark btn-sm">
                                    <i class="fa-light fa-plus"></i> Nueva proforma
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="proformas_search" class="form-control form-control-round"
                                    placeholder="ingrese numero o cliente para Buscar....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">fecha</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">total</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($proformas as $proforma)
                                    <tr id="{{ $proforma->id }}">
                                        <td class="align-middle">{{ $proforma->numero }}</td>
                                        <td class="align-middle">{{ $proforma->fecha_formatted }}</td>
                                        <td class="align-middle">{{ $proforma->cliente->nombre }}</td>
                                        <td class="align-middle">{{ $proforma->total_formatted }}</td>
                                        <td class="align-middle">
                                            <div class="btn-group dropleft">
                                                <button type="button" class="btn btn-secondary dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Opciones
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href='{{ route('proformas.edit', [$tipo, $proforma->id]) }}'
                                                        class='dropdown-item'>Editar</a>
                                                    <a href='javascript:void(0);' class='dropdown-item eliminar'
                                                        id='{{ $proforma->id }}'>Eliminar</a>
                                                    <a href='{{ route('pdf.proformas', [$tipo, $proforma->id]) }}'
                                                        class='dropdown-item' target="_blank">generar pdf</a>
                                                </div>
                                            </div>
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

                    @include('partials.pagination', ['paginator' => $proformas, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>

    {{ Form::hidden('tipo_proforma', $tipo, ['id' => 'tipo-proforma']) }}

@endsection

@section('scripts')
    <script src="{{ asset('js/proformas.js?v=' . config('app.version', '')) }}"></script>
@endsection
