@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container-fluid">
            {!! Form::open([
                'route' => ['pdf.reporte.adquisiciones', ''],
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
                            <h5 class="col card-title">reporte de adquisiciones</h5>
                            <div class="col-auto ">
                                <button type="button" class="btn btn-dark form-group" id="visualizar-reporte">Generar</button>
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
                            <label for="" class="col-form-label">SubProyecto </label>
                            {{ Form::select('subproyecto', [], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione subproyecto', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Etapa </label>
                            {{ Form::select('etapa', $etapas, '', ['class' => 'form-control', 'data-placeholder' => 'seleccione etapa', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Tipo adquisicion </label>
                            {{ Form::select('tipo', $tipoAdquisiciones, '', ['class' => 'form-control', 'data-placeholder' => 'seleccione tipo', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Proveedores </label>
                            {{ Form::select('proveedor', [], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione proveedor', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Productos </label>
                            {{ Form::select('producto', [], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione producto', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Cargos </label>
                            {{ Form::select('cargo', [], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione cargo', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Necesidad </label>
                            {{ Form::select('necesidad', $necesidades, '', ['class' => 'form-control', 'data-placeholder' => 'seleccione necesidad', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Costos </label>
                            {{ Form::select('costo', ['' => ''] + ['directos' => 'directos', 'indirectos' => 'indirectos'], '', ['class' => 'form-control', 'data-placeholder' => 'seleccione costo', 'data-allow-clear' => 'true']) }}
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label">Tipo reporte </label>
                            {{ Form::select(
                                'tipo_reporte',
                                ['' => ''] + ['administrativo' => 'Administrativo', 'operativo' => 'Operativo', 'global' => 'Global'],
                                '',
                                ['class' => 'form-control', 'data-placeholder' => 'Seleccione tipo reporte', 'data-allow-clear' => 'true'],
                            ) }}
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
