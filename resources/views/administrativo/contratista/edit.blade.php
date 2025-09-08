@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            @include('partials.alerts')
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="col-md-10">
                                <h5>Orden de trabajo Nro. {{ numeroOrden($orden_trabajo, false) }}</h5>
                                <span>Fecha. {{ dateFormatHumans($orden_trabajo->fecha) }} </span>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_orden_trabajo">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    {!! Form::model($orden_trabajo, [
                        'route' => [
                            'administrativo.contratista.update',
                            [
                                'contratista' => $orden_trabajo->id,
                            ],
                        ],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_orden_trabajo',
                        'method' => 'PUT',
                    ]) !!}

                    @include('contratista.partials.form')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
