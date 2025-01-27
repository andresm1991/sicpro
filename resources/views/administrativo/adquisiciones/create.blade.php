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
                            <div class="col-md-8">
                                <h4>Orden de Pedido #{{ $numero_orden }}</h4>
                                <h6>Fecha: {!! Form::text('fecha', old('fecha', date('Y-m-d')), ['class' => 'auto-ajustable', 'readonly' => true]) !!} <i class="fa-regular fa-calendar-days datepicker-2"
                                        id="fecha"></i></h6>
                            </div>

                            <div class="col-md-2 ">
                                <div class="select_wrapper">
                                    <label class="rounded  text-white">
                                        <input type="checkbox" name="orden_completa" class="d-none" value="true"
                                            {{ $adquisicion->estado == 'Completado' ? 'checked' : '' }}
                                            {{ $tipo == 'operativo' && $adquisicion->estado == 'Completado' ? 'disabled' : '' }}>
                                        <span
                                            class="text-center d-block py-3">{{ isset($adquisicion->tipo_etapa) && $adquisicion->tipo_etapa->descripcion == 'Servicios' ? 'Pedido Pagado' : 'Pedido Completo' }}
                                        </span>
                                    </label>
                                </div>
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
                            'administrativo.adquisiciones.store',
                            [
                                'tipo' => $tipo,
                            ],
                        ],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_order_pedido',
                    ]) !!}
                    {{ Form::hidden('numero_orden', $numero_orden) }}
                    @include('administrativo.adquisiciones.partials.form')
                    @include('administrativo.adquisiciones.partials.items')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>

@endsection
