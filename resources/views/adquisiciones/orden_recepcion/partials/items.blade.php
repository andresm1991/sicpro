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
                        {{ Form::text('cantidad_recibida', old('cantidad_recibida', $detalle->cantidad_recibida), ['class' => 'form-control solo-numeros', !$orden_recepcion->editar ? 'disabled' : '']) }}
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
                    {{ old('forma_pago') == $key || (isset($orden_recepcion->forma_pago->id) && $orden_recepcion->forma_pago->id == $key) ? 'checked' : '' }}
                    {{ !$orden_recepcion->editar ? 'disabled' : '' }}>
                <span class="text-center d-block py-3">{{ $value }}</span>
            </label>
        @empty
            <small class="help-block text-danger error_mensajes">No existen formas de pagos
                registrados.</small>
        @endforelse

    </div>
    {!! $errors->first('forma_pago', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
</div>
