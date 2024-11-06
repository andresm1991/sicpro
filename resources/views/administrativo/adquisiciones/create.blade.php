@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fuild">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-10">
                                <h4>Orden de Pedido #{{ $numero_orden }}</h4>
                                <h6>Fecha: {!! Form::text('fecha', old('fecha', date('Y-m-d')), ['class' => 'auto-ajustable', 'readonly' => true,]) !!} <i class="fa-regular fa-calendar-days datepicker-2" id="fecha"></i></h6>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_order_pedido">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::open([
                        'route' => [
                            'administrativo.adquisiciones.create',
                            [
                                'tipo' => $tipo,
                            ],
                        ],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_order_pedido',
                    ]) !!}
                        @include('administrativo.adquisiciones.partials.form')
                        @include('administrativo.adquisiciones.partials.items')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>

@endsection
