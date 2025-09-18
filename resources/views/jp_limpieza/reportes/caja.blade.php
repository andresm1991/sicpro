@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container-fluid">
            {!! Form::open([
                'route' => ['pdf.reporte.jp.limpieza.caja', ''],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form-reporte',
                'target' => '_blank',
            ]) !!}

            {{ Form::hidden('tipo_reporte', 'jp_limpieza', ['id' => 'tipo_reporte']) }}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <h5 class="col card-title">reporte Caja</h5>
                            <div class="col-auto ">
                                <button type="button" class="btn btn-dark form-group"
                                    id="generar-reporte-caja">Generar</button>
                                <button type="button" class="btn btn-dark form-group generar-reporte"
                                    data-action="pdf">Exportar a pdf</button>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="form-group col-sm-4">
                            <label for="" class="col-form-label ">Rango de Fechas </label>
                            {{ Form::text('fechas', '', ['class' => 'form-control daterange', 'id' => 'fechas', 'placeholder' => 'seleccione rago de fechas']) }}
                        </div>

                        @if (auth()->user()->hasRole(['Administrador', 'Gerencial']))
                            <div class="form-group col-sm-4">
                                <label for="lbl_ultima-revision" class="col-form-label ">Fecha última revisión </label>
                                <input type="text" class="form-control" id="ultima-revision"
                                    value="{{ $ultima_revision_caja->fecha_revision_inicio_formatted ?? 'sin revisión' }} - {{ $ultima_revision_caja->fecha_revision_fin_formatted ?? 'sin revisión' }}"
                                    placeholder="sin revisión" disabled>
                            </div>

                            <div class=" d-flex align-items-center col-sm-4 mt-4 p-0">
                                <button type="button" class="btn btn-dark" id="guardar-revision-caja">
                                    Finalizar Revisión
                                </button>
                            </div>
                        @endif
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
