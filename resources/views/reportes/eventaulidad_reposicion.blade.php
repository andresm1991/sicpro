@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container-fluid">
            {!! Form::open([
                'route' => ['pdf.reporte.solicitudes', ''],
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
                            <h5 class="col card-title">reporte Solicitudes</h5>
                            <div class="col-auto ">
                                <button type="button" class="btn btn-dark form-group"
                                    id="visualizar-reporte-solicitudes">Generar</button>
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
                            <label for="" class="col-form-label ">Tipo Solicitud</label>
                            {{ Form::select('tipo_solicitud', $tipo_solicitudes, '', ['class' => 'form-control', 'data-placeholder' => 'seleccione opción', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label ">Colaboradores</label>
                            {{ Form::select('usuario', usuariosPluck(true), '', ['class' => 'form-control', 'data-placeholder' => 'seleccione opción', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label ">Fecha</label>
                            {{ Form::text('fechas', '', ['class' => 'form-control daterange', 'placeholder' => 'seleccione rago de fechas']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label ">Recuperable</label>
                            {{ Form::select('recuperable', ['' => '', 'si' => 'si', 'no' => 'no'], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione opción', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label ">Estado</label>
                            {{ Form::select('estado', $estados, '', ['class' => 'form-control', 'data-placeholder' => 'seleccione opción', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label class="col-form-label">Ordenado por</label>
                            <div class="col-12 p-0">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ordenado" id="inlineRadio1"
                                        value="secuencial" checked>
                                    <label class="form-check-label" for="inlineRadio1"> # secuenial</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ordenado" id="inlineRadio2"
                                        value="fecha">
                                    <label class="form-check-label" for="inlineRadio2"> Fecha</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ordenado" id="inlineRadio3"
                                        value="alfabetico">
                                    <label class="form-check-label" for="inlineRadio3">Alfabético</label>
                                </div>
                            </div>
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
    <script src="{{ asset('js/reportes_scripts.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection
