<div class="table-responsive">
    <table class="table table-bordered table-hover" id="tabla-planificacion">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Unidad</th>
                <th scope="col">Precio Unitario</th>
                @if (isset($administrativo))
                    <th scope="col">costo Indirecto</th>
                @endif
                <th scope="col">Total</th>
                @if (!isset($administrativo))
                    <th class="table-actions"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($orden_trabajo->detalle_contratistas as $index => $detalle)
                <tr class="elementos-agregados">
                    <td class="align-middle">{{ $index + 1 }}</td>
                    <td class="align-middle">
                        {{ $detalle->articulo->descripcion }}
                        <input type="hidden" name="productos[]" value="{{ $detalle->articulo->id }}">
                    </td>
                    <td class="align-middle" style="width: 1px">
                        <input type="text" class="form-control mr-2 input-double" name="cantidad[]"
                            value="{{ $detalle->cantidad }}">
                    </td>
                    <td class="align-middle">
                        <span>{{ $detalle->unidad_medida->descripcion }}</span>
                        {{ Form::hidden('unidad_medida[]', $detalle->unidad_medida_id) }}
                    </td>
                    <td class="align-middle" style="width: 1px">
                        <input type="text" class="form-control mr-2 currency" name="precio_unitario[]"
                            value="{{ $detalle->valor_unitario }}">
                    </td>
                    @if (isset($administrativo))
                        <td class="align-middle" style="width: 1px">
                            <input type="text" class="form-control mr-2 input-double" name="costo_indirecto[]"
                                value="{{ $detalle->costo_indirecto }}">
                        </td>
                    @endif
                    <td class="align-middle total_unitario"><span class="total">$
                            {{ $detalle->total_items_formatter }}</span>
                    </td>
                    @if (!isset($administrativo))
                        <td class="align-middle table-actions">
                            <div class="action-buttons">
                                <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto"
                                    id=""><i class="fa-solid fa-trash-can"></i></a>
                            </div>
                        </td>
                    @endif

                </tr>
            @endforeach
            <tr id="tr-default" style="display:{{ $orden_trabajo->detalle_contratistas->isEmpty() ? '' : 'none' }}">
                <td colspan="7" class="text-center">
                    <span>No existen elementos agregados</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    <div class="row ">
        <div class="col-12">
            <div class="form-group row text-right">
                <label for="staticEmail" class="col-sm-6 col-form-label">SubTotal: </label>
                <div class="col-sm-4">
                    <input type="text" readonly class="form-control-plaintext" id="subtotal"
                        value="${{ isset($subTotal) ? number_format($subTotal, 4) : '0.00' }}">
                </div>
            </div>

            <div class="form-group row text-right">
                <label for="inputPassword" class="col-sm-6 col-form-label">Nro. Casas: </label>
                <div class="col-sm-4 ">
                    {{ Form::text('numero_casas', old('numero_casas', $orden_trabajo->numero_casas), ['class' => 'form-control input-enteros', 'placeholder' => '0', 'id' => 'numero_casas']) }}
                </div>
            </div>

            <div class="form-group row text-right">
                <label for="staticEmail" class="col-sm-6 col-form-label">Total General: </label>
                <div class="col-sm-4">
                    <input type="text" readonly class="form-control-plaintext" id="total-general"
                        value="${{ isset($totalGeneral) ? number_format($totalGeneral, 4) : '0.00' }}">
                </div>
            </div>
        </div>
    </div>
</div>
