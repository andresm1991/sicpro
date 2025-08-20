@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            {!! Form::model($proforma, [
                'route' => [
                    $tipo == 'adecentamientos' ? 'proformas.update.adecentamientos' : 'proformas.update.planos',
                    ['tipo' => $tipo, 'id' => $proforma->id],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_proforma',
                'method' => 'PUT',
            ]) !!}
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4 class="font-weight-bolder">Proforma nro.
                                    {{ $proforma->numero }}</h4>

                            </div>
                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block guardar" id="nuevo">Guardar
                                    nuevo</button>
                            </div>
                            <div class="col-md-2 ">
                                <button class="btn btn-secondary btn-options btn-block guardar"
                                    id="actualizar">actualizar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('proformas.partials.form')
                    @include('proformas.partials.items')
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </section>

@endsection
