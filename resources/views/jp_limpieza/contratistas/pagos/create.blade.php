@extends('layouts.app')

@section('title', 'nuevo contratista')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            {!! Form::open([
                'route' => [
                    'jp.limpieza.contratistas.pagos.store',
                    [
                        'proyecto' => $proyecto,
                        'contratista' => $contratista->id,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_pagos_contratista',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>orden de trabajo #{{ $contratista->numero }}</h4>
                                <h6>Fecha: {!! Form::text('fecha', old('fecha', date('Y-m-d')), ['class' => 'auto-ajustable', 'readonly' => true]) !!} <i class="fa-regular fa-calendar-days datepicker-2"
                                        id="fecha"></i></h6>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block"
                                    form="form_pagos_contratista">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    @include('jp_limpieza.contratistas.pagos.partials.form')
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </section>

@endsection
@section('scripts')
    <script src="{{ asset('js/jp_limpieza/adquisiciones.js') }}"></script>
@endsection
