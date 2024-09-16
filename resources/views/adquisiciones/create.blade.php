@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-10">
                                <h4>Orden de Pedido #{{ $numero_orden }}</h4>
                                <h6>Fecha: {{ date('Y-m-d') }}</h6>
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
                            'proyecto.adquisiciones.store',
                            [
                                'tipo' => $tipo,
                                'tipo_id' => $tipo_id,
                                'tipo_adquisicion' => $tipo_adquisicion->id,
                                'tipo_etapa' => $tipo_etapa->id,
                                'proyecto' => $proyecto->id,
                            ],
                        ],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_order_pedido',
                    ]) !!}
                    {{ Form::hidden('numero_pedido', $numero_orden) }}
                    {{ Form::hidden('proyecto_id', $proyecto->id) }}
                    {{ Form::hidden('tipo_adquisicion', $tipo_adquisicion->id) }}
                    {{ Form::hidden('tipo_etapa', $tipo_etapa->id) }}

                    @include('adquisiciones.partials.form')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>

@endsection
