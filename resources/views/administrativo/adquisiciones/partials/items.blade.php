<div class="table-responsive" id="table">
    <table class="table table-bordered table-hover" id="table-adquisiciones">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col" class="text-center">Cantidad</th>
                <th scope="col" class="text-center">Unidad Medida</th>
                <th scope="col">Valor Unitario</th>
                <th scope="col">Total</th>    
                <th scope="col">Necesidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($adquisicion->adquisiciones_detalle as $index => $detalle)
                <tr>
                    <td class="align-middle">{{ $index + 1 }}</td>
                    <td class="align-middle">{{ $detalle->producto->descripcion }}</td>
                    <td class="align-middle text-center cantidad" data-index="{{ $index }}">{{ $detalle->cantidad_recibida }}</td>
                    <td class="align-middle">
                        {{ Form::select('unidad_medida[' . $index . ']', getUnidadMedidas(true), $detalle->unidad_medida_id, ['class' => 'form-control col-sm-12 select2-tag' , 'data-placeholder' => 'Selecione']) }}

                        {!! $errors->first('unidad_medida.' . $index, '<small class="help-block text-danger error_mensajes">:message</small>') !!}
                    </td>
                    <td class="align-middle col-md-1 col-12">
                        {{ Form::text('valor[' . $index . ']', old('valor.' . $index, $detalle->valor), ['class' => 'form-control currency precio-unitario', 'placeholder' => '$ 0.00', 'data-index' => $index]) }}
                        
                        {!! $errors->first('valor.' . $index, '<small class="help-block text-danger error_mensajes">:message</small>') !!}
                    </td>
                    <td class="align-middle calculo-total" data-index="{{ $index }}">$ {{ number_format(($detalle->cantidad_recibida *  $detalle->valor), 2)}}</td>
                    <td class="align-middle">{{ $detalle->necesidad }}</td>
                </tr>
            @endforeach
        </tbody>

    </table>

    <div class="select_wrapper">
        <div>
            <h5>Forma de pago.</h5>
        </div>
        @forelse (formasPagos() as $key => $value)
            <label class="rounded-0 text-white">
                <input type="radio" name="forma_pago" class="d-none" value="{{ $key }}"
                    {{ $adquisicion->orden_recepcion->forma_pago->id == $key ? 'checked' : '' }} disabled>
                <span class="text-center d-block py-3">{{ $value }}</span>
            </label>
        @empty
            <small class="help-block text-danger error_mensajes">No existen formas de pagos
                registrados.</small>
        @endforelse
    </div>
</div>
