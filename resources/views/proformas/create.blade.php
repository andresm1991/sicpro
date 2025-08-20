@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            {!! Form::open([
                'route' => [$tipo == 'adecentamientos' ? 'proformas.store.adecentamientos' : 'proformas.store.planos', $tipo],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_proforma',
            ]) !!}
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4 class="font-weight-bolder">Proforma nro.
                                    {{ numeroProforma($numero) }}</h4>

                            </div>
                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block guardar" form="form_proforma"
                                    id="nuevo">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    {{ Form::hidden('numero', numeroProforma($numero)) }}
                    @include('proformas.partials.form')
                    @include('proformas.partials.items')
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </section>

@endsection
