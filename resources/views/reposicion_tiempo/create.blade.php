@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')

    <section class="content" style="padding-bottom: 20px;">
        <div class="container-fluid">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="col-md-10">
                                <h4 class="mt-2 font-weight-bold">Formulario de Reposición</h4>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" id="btn-guardar" form="form_reposicion"
                                    disabled>Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::open([
                        'route' => ['solicitud.reposicion.store'],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_reposicion',
                    ]) !!}

                    @include('reposicion_tiempo.partials.form')

                    {!! Form::close() !!}

                    <div class="col-12 mt-4 p-0">
                        <p class="font-italic">Los campos con (*) son obligatorios, por favor complétalos corretéame</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
