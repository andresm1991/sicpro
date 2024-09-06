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

                    <div class="row">
                        <div class="col-md-7 col-12">
                            <div class="form-group row">
                                {{ Form::label('', 'Fecha', ['class' => 'col-sm-2 col-form-label']) }}
                                <div class="col-sm-4">
                                    {{ Form::label('', date('Y-m-d'), ['class' => 'form-control text-capitalize', 'readonly']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-12">
                            <div class="form-group row">
                                {{ Form::label('', 'Etapa', ['class' => 'col-sm-3 col-form-label']) }}
                                <div class="col-sm-9">
                                    {{ Form::text('etapa', $tipo_adquisicion->descripcion, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-7 col-12">
                            <div class="form-group row">
                                {{ Form::label('', 'Proyecto', ['class' => 'col-sm-2 col-form-label']) }}
                                <div class="col-sm-10">
                                    {{ Form::text('proyecto', $proyecto->nombre_proyecto, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-12">
                            <div class="form-group row">
                                {{ Form::label('', 'Proveedor', ['class' => 'col-sm-3 col-form-label']) }}
                                <div class="col-sm-9">
                                    <select name="proveedor" class="form-control select2-basic-single"
                                        data-placeholder="Selecione proveedor">
                                        <option></option>
                                        @foreach ($proveedores as $id => $nombre)
                                            <option value="{{ $id }}"
                                                {{ old('proveedor') == $id || (isset($orden_recepcion->proveedor_id) && $orden_recepcion->proveedor_id == $id) ? 'selected' : '' }}>
                                                {{ $nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('proveedor', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Elementos de la orden de pedido --}}
                    <div class="table-responsive" id="table">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Cantidad Pedida</th>
                                    <th scope="col">Cantidad Recibida</th>
                                    <th scope="col">Necesidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedido->adquisiciones_detalle as $index => $detalle)
                                    <tr>
                                        <td class="align-middle">{{ $index + 1 }}</td>
                                        <td class="align-middle">{{ $detalle->producto->descripcion }}</td>
                                        <td class="align-middle">{{ $detalle->cantidad_solicitada }}</td>
                                        <td class="align-middle">
                                            {{ Form::text('cantidad_recibida', old('cantidad_recibida', $detalle->cantidad_recibida), ['class' => 'form-control solo-numeros']) }}
                                            {!! $errors->first('cantidad_recibida', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
                                        </td>
                                        <td class="align-middle">{{ $detalle->necesidad }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                        <div class="select_wrapper">
                            <div>
                                <h5>Forma de pago.</h5>
                            </div>
                            @forelse ($forma_pagos as $key => $value)
                                <label class="rounded-0 text-white">
                                    <input type="radio" name="forma_pago" class="d-none" value="{{ $key }}"
                                        {{ old('forma_pago') == $key || (isset($orden_recepcion->forma_pago->id) && $orden_recepcion->forma_pago->id == $key) ? 'checked' : '' }}>
                                    <span class="text-center d-block py-3">{{ $value }}</span>
                                </label>
                            @empty
                                <small class="help-block text-danger error_mensajes">No existen formas de pagos
                                    registrados.</small>
                            @endforelse

                        </div>
                        {!! $errors->first('forma_pago', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
                    </div>

                </div>
            </div>
            {{ Form::close() }}
        </div>
    </section>
@endsection
