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
