<div class="table-responsive" id="table">
    <table class="table table-bordered table-hover" id="table-adquisiciones">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col" class="text-center">Cantidad</th>
                <th scope="col" class="text-center">Unidad Medida</th>
                <th scope="col">Valor Unitario</th>
                <th scope="col">% IVA</th>
                <th scope="col">Total</th>
                <th scope="col">Necesidad</th>
                @if ($tipo == 'administrativo')
                    <th scope="col" class="text-center">Inventario</th>
                    <th></th>
                @endif

            </tr>
        </thead>
        <tbody>
            @foreach ($adquisicion->adquisiciones_detalle as $index => $detalle)
                <tr class="elementos-agregados">
                    <td class="align-middle">{{ $index + 1 }}</td>
                    <td class="align-middle">{{ $detalle->producto->descripcion }}</td>
                    <td class="align-middle text-center cantidad" data-index="{{ $index }}">
                        {{ $detalle->cantidad_solicitada }}

                        {{ Form::hidden('cantidad[' . $index . ']', $detalle->cantidad_solicitada) }}
                    </td>

                    <td class="align-middle">
                        {{ Form::select('unidad_medida[' . $index . ']', getUnidadMedidas(true), $detalle->unidad_medida_id, ['class' => 'form-control col-sm-12 select2-tag', 'data-placeholder' => 'Selecione']) }}

                        {!! $errors->first(
                            'unidad_medida.' . $index,
                            '<small class="help-block text-danger error_mensajes">:message</small>',
                        ) !!}
                    </td>
                    <td class="align-middle col-md-1 col-12">
                        {{ Form::text('valor[' . $index . ']', old('valor.' . $index, $detalle->valor), ['class' => 'form-control currency precio-unitario', 'data-valor-actual' => $detalle->producto->valor_unitario, 'placeholder' => '$ 0.00', 'data-index' => $index]) }}

                        {!! $errors->first('valor.' . $index, '<small class="help-block text-danger error_mensajes">:message</small>') !!}
                    </td>
                    <td class="align-middle col-md-1 col-12">
                        {{ Form::text('iva_producto[' . $index . ']', old('iva_producto.' . $index, $detalle->producto->iva), ['class' => 'form-control col-sm-12 input-enteros iva-producto', 'placeholder' => '0', 'data-index' => $index]) }}

                        {!! $errors->first(
                            'iva_producto.' . $index,
                            '<small class="help-block text-danger error_mensajes">:message</small>',
                        ) !!}
                    </td>
                    <td class="align-middle calculo-total" data-index="{{ $index }}">$
                        {{ calcularTotalProducto($detalle->cantidad_solicitada, $detalle->valor, $detalle->producto->iva) }}
                    </td>
                    <td class="align-middle">
                        {{ $detalle->necesidad }}
                        {{ Form::hidden('necesidad[' . $index . ']', $detalle->necesidad) }}
                    </td>
                    @if ($tipo == 'administrativo')
                        <td class="align-middle">
                            <div class="checkbox-wrapper-8 d-flex justify-content-center align-items-center">
                                {{ Form::hidden('inventario[' . $index . ']', 0) }}
                                <input class="tgl tgl-skewed inventario" name="inventario[{{ $index }}]"
                                    id="cb3-{{ $index }}" type="checkbox" value="0"
                                    {{ isset($pedido->orden_recepcion->inventario) && $pedido->orden_recepcion->inventario->pluck('producto_id')->contains($detalle->articulo_id) ? 'checked' : '' }}
                                    {{ $tipo == 'operativo' ? 'disabled' : (isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '') }} />
                                <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI"
                                    for="cb3-{{ $index }}"></label>
                            </div>
                        </td>
                        <td class="align-middle table-actions">
                            <div class="action-buttons">
                                <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto"
                                    id=""><i class="fa-solid fa-trash-can"></i></a>
                            </div>
                        </td>

                        <input type="hidden" name="productos[]" value="{{ $detalle->articulo_id }}">
                    @endif
                </tr>
            @endforeach

            <tr id="tr-default" style="display:{{ $adquisicion->id ? 'none' : '' }}">
                <td colspan="10" class="text-center">No existen elementos en la lista...</td>
            </tr>
        </tbody>

    </table>
    <div class="col-12 text-right p-0 py-4">
        <h5>Total General: <span
                id="total-general">${{ isset($totalGeneral) ? number_format($totalGeneral, 4) : '0.0000' }}</span></h5>
    </div>
    <br>

    <div class="select_wrapper">
        <div>
            <h5>Forma de pago.</h5>
        </div>
        @forelse (formasPagos() as $key => $value)
            <label class="rounded-0 text-white">
                <input type="radio" name="forma_pago" class="d-none" value="{{ $key }}"
                    {{ isset($adquisicion->orden_recepcion->forma_pago->id) && $adquisicion->orden_recepcion->forma_pago->id == $key ? 'checked' : '' }}
                    {{ $tipo == 'operativo' ? 'disabled' : '' }}>
                <span class="text-center d-block py-3">{{ $value }}</span>
            </label>
        @empty
            <small class="help-block text-danger error_mensajes">No existen formas de pagos
                registrados.</small>
        @endforelse
    </div>
    {!! $errors->first('forma_pago', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
</div>
