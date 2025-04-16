@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container-fluid">
            {!! Form::open([
                'route' => ['pdf.reporte.gasolina.camnoneta', ''],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form-reporte',
                'target' => '_blank',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <h5 class="col card-title">reporte gasolina para camioneta</h5>
                            <div class="col-auto ">
                                <button type="button" class="btn btn-dark form-group"
                                    id="visualizar-reporte-gasolina">Generar</button>
                                <button type="button" class="btn btn-dark form-group generar-reporte"
                                    data-action="pdf">Exportar a
                                    pdf</button>
                                <button type="button" class="btn btn-dark form-group generar-reporte"
                                    data-action="excel">Exportar a
                                    excel</button>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="card-body">

                    <div class="row align-items-center">
                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label ">Fecha</label>
                            {{ Form::text('fechas', '', ['class' => 'form-control daterange', 'placeholder' => 'seleccione rago de fechas']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Estados</label>
                            {{ Form::select('estado', ['' => ''] + ['completados' => 'completados', 'pendientes' => 'pendientes'], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione estado', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Proyecto </label>
                            {{ Form::select('proyecto', $proyectos, '', ['class' => 'form-control', 'data-placeholder' => 'seleccione proyecto', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Necesidad </label>
                            {{ Form::select('necesidad', $necesidades, '', ['class' => 'form-control', 'data-placeholder' => 'seleccione necesidad', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Forma Pago </label>
                            <select name="forma_pago" id="forma-pago" class="form-control"
                                data-placeholder="seleccione opcón" data-allow-clear='true'>
                                <option value=""></option>
                                @foreach (formasPagos() as $index => $nombre)
                                    <option value="{{ $index }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="table-view-reporte"></div>
                </div> {{-- end div car body --}}
            </div>
            {{ Form::close() }}
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/reportes_scripts.js') }}" type="module"></script>
@endsection
