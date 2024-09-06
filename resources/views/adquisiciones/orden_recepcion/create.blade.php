@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container">
            {!! Form::open([
                'route' => [
                    'proyecto.adquisiciones.orden.recepcion.store',
                    [
                        'tipo' => $tipo,
                        'tipo_id' => $tipo_id,
                        'tipo_adquisicion' => $tipo_adquisicion->id,
                        'tipo_etapa' => $tipo_etapa->id,
                        'proyecto' => $proyecto->id,
                        'pedido' => $pedido->id,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_order_recepcion',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>Orden de Pedido #{{ $pedido->numero }}</h4>
                            </div>
                            <div class="col-md-2 ">
                                <div class="select_wrapper">
                                    <label class="rounded  text-white">
                                        <input type="checkbox" name="orden_completa" class="d-none" value="true"
                                            {{ old('orden_completa') == true || (isset($orden_recepcion->completado) && $orden_recepcion->completado == true) ? 'checked' : '' }}>
                                        <span class="text-center d-block py-3">Pedido Completo</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block"
                                    form="form_order_recepcion">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {{ Form::hidden('pedido', $pedido->id) }}

                    @include('adquisiciones.orden_recepcion.partials.form')
                    @include('adquisiciones.orden_recepcion.partials.items')

                </div>
            </div>
            {{ Form::close() }}
        </div>
    </section>
@endsection
