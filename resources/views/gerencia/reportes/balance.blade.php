@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container-fluid">
            <form id="form-reporte-balance-gerencial" method="POST" action="">

                <div class="card">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="row">
                                <h5 class="col card-title">reporte de balance</h5>
                                <div class="col-auto ">
                                    <button type="button" class="btn btn-dark form-group"
                                        id="generar-reporte-balance-gerencial">Generar</button>
                                    <button type="button" class="btn btn-dark form-group generar-reporte"
                                        data-action="pdf">Exportar a
                                        pdf</button>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <div class="card-body">

                        <div class="row align-items-center">
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
                            <div class="form-group col-sm-4">
                                <label for="" class="col-form-label">Tipo reporte </label>
                                {{ Form::select(
                                    'tipo_reporte',
                                    ['' => ''] + [
                                        'balance_global' => 'Balance Global',
                                        'balance_proyecto' => 'Balance por Proyecto',
                                    ],
                                    '',
                                    ['class' => 'form-control', 'data-placeholder' => 'Seleccione tipo reporte', 'data-allow-clear' => 'true'],
                                ) }}
                            </div>

                            <div class="form-group col-sm-4" id="contenedor-fechas">
                                <label for="" class="col-form-label ">Fecha</label>
                                {{ Form::text('fechas', '', ['class' => 'form-control daterange', 'placeholder' => 'seleccione rago de fechas']) }}
                            </div>

                            <div class="form-group col-sm-4" id="contenedor-anios">
                                <label for="" class="col-form-label ">Año</label>
                                {{ Form::select('anio', ['' => ''] + getAnios(), '', ['class' => 'form-control', 'data-placeholder' => 'seleccione año', 'data-allow-clear' => 'true']) }}
                            </div>

                            <div class="form-group col-sm-4">
                                <label for="" class="col-form-label">Proyecto </label>
                                {{ Form::select('proyecto', getProyectos(), '', ['class' => 'form-control', 'id' => 'select-proyecto', 'data-placeholder' => 'seleccione proyecto', 'data-allow-clear' => 'true']) }}
                            </div>

                            <div class="form-group col-sm-4">
                                <label for="" class="col-form-label">SubProyecto </label>
                                {{ Form::select('subproyecto', [], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione subproyecto', 'data-allow-clear' => 'true']) }}
                            </div>

                            <div class="form-group col-sm-4">
                                <label for="" class="col-form-label">Etapa </label>
                                {{ Form::select('etapa', getEtapasProyecto(), '', ['class' => 'form-control', 'data-placeholder' => 'seleccione etapa', 'data-allow-clear' => 'true']) }}
                            </div>

                            <div class="form-group col-sm-4">
                                <label for="" class="col-form-label">Tipo adquisición </label>
                                {{ Form::select('tipo_etapa', getTipoEtapasProyecto(), '', ['class' => 'form-control', 'data-placeholder' => 'seleccione tipo adquisición', 'data-allow-clear' => 'true']) }}
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
    <script src="{{ asset('js/gerencial_scripts.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection
