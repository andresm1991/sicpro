{{-- Elementos de la orden de pedido --}}
<div class="table-responsive" id="table">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad Pedida</th>
                <th scope="col">Cantidad Recibida</th>
                <th scope="col">Unidad</th>
                <th scope="col">Valor</th>
                <th scope="col">Necesidad</th>
                <th scope="col" class="text-center">Inventariar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pedido->adquisiciones_detalle as $index => $detalle)
                <tr>
                    <td class="align-middle">{{ $index + 1 }}</td>
                    <td class="align-middle">{{ $detalle->producto->descripcion }}</td>
                    <td class="align-middle">{{ $detalle->cantidad_solicitada }}</td>
                    <td class="align-middle">
                        {{ Form::text('cantidad_recibida[' . $index . ']', old('cantidad_recibida.' . $index, $detalle->cantidad_recibida), ['class' => 'form-control input-double', isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '']) }}
                        {!! $errors->first(
                            'cantidad_recibida.' . $index,
                            '<small class="help-block text-danger error_mensajes">:message</small>',
                        ) !!}
                    </td>
                    <td class="align-middle">
                        {{ Form::select('unidad_medida[' . $index . ']', $unidad_medidas, $detalle->unidad_medida_id, ['class' => 'form-control col-auto', 'data-placeholder' => 'Selecione', isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '']) }}

                        {!! $errors->first(
                            'unidad_medida.' . $index,
                            '<small class="help-block text-danger error_mensajes">:message</small>',
                        ) !!}
                    </td>
                    <td class="align-middle col-md-1 col-12">
                        {{ Form::text('valor[' . $index . ']', old('valor.' . $index, $detalle->valor), ['class' => 'form-control input-double ', isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '']) }}
                        {!! $errors->first('valor.' . $index, '<small class="help-block text-danger error_mensajes">:message</small>') !!}
                    </td>
                    <td class="align-middle">{{ $detalle->necesidad }}</td>
                    <td class="align-middle">
                        <div class="checkbox-wrapper-8 d-flex justify-content-center align-items-center">
                            {{ Form::hidden('inventario[' . $index . ']', 0) }}
                            <input class="tgl tgl-skewed inventario" name="inventario[{{ $index }}]"
                                id="cb3-{{ $index }}" type="checkbox" value="0"
                                {{ isset($pedido->orden_recepcion->inventario) && $pedido->orden_recepcion->inventario->pluck('producto_id')->contains($detalle->articulo_id) ? 'checked' : '' }}
                                {{ isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '' }} />
                            <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI"
                                for="cb3-{{ $index }}"></label>
                        </div>
                    </td>
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
                    {{ old('forma_pago') == $key || (isset($orden_recepcion->forma_pago->id) && $orden_recepcion->forma_pago->id == $key) ? 'checked' : '' }}
                    {{ isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '' }}>
                <span class="text-center d-block py-3">{{ $value }}</span>
            </label>
        @empty
            <small class="help-block text-danger error_mensajes">No existen formas de pagos
                registrados.</small>
        @endforelse
    </div>
    {!! $errors->first('forma_pago', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
</div>
