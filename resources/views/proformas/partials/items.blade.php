<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-sm-8">
            <div class="form-group">
                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                <div id="select-container">
                    {{ Form::select('', getProdutosProformas($tipo), '', ['class' => 'form-control select2-tag', 'data-placeholder' => 'selecciona producto', 'id' => 'producto']) }}
                    <div class="mt-2"></div>
                    <button type="button" id="btnEditar" class="btn btn-secondary" style="display: none;">Editar
                        producto</button>
                </div>

                <!-- Contenedor para el modo edición (oculto por defecto) -->
                <div id="edit-container" style="display: none;">
                    <input type="text" id="inputTextEditar" class="form-control" style="width: 100%;">
                    <div class="mt-2"></div>
                    <button type="button" id="btnGuardar" class="btn btn-dark">Guardar</button>
                    <button type="button" id="btnCancelar" class="btn btn-secondary">Cancelar</button>
                </div>
            </div>


        </div>
        <div class="col-sm-2">
            <div class="form-group">
                @if ($tipo == 'adecentamientos')
                    {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                    {{ Form::text('', 0, ['class' => 'form-control input-double', 'id' => 'cantidad']) }}
                @else
                    {{ Form::label('', 'estimado M2', ['class' => 'col-form-label']) }}
                    {{ Form::text('', 0, ['class' => 'form-control input-double', 'id' => 'area_m2']) }}
                @endif

            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'V.Unit', ['class' => 'col-form-label']) }}
                {{ Form::text('', 0, ['class' => 'form-control moneyDosDecimales', 'id' => 'valor_unitario']) }}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'Costo Indirecto', ['class' => 'col-form-label']) }}
                {{ Form::text('', 0, ['class' => 'form-control input-enteros', 'id' => 'costo-indirecto']) }}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'total', ['class' => 'col-form-label']) }}
                {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'total', 'placeholder' => '$ 0.0000', 'disabled' => true]) }}
            </div>
        </div>

        <div class="col-sm-2 d-flex align-items-end">
            <div class="form-group">
                <button type="button" class="btn btn-dark" id="agregar-producto">Agregar producto</button>
            </div>
        </div>
    </div>
</fieldset>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">{{ $tipo == 'adecentamientos' ? 'Cantidad' : 'estimado m2' }}</th>
                <th scope="col">V.Unit</th>
                <th scope="col">% indirecto</th>
                <th scope="col">total</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tipo == 'adecentamientos' ? $proforma->detalleAdecentamientos : $proforma->detallePlanos as $index => $detalle)
                <tr class="elementos-agregados" data-producto-id="{{ $detalle->producto_id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $detalle->producto->nombre }}
                        <input type="hidden" name="producto[]" value="{{ $detalle->producto_id }}">
                    </td>
                    <td class="cantidad-celda">
                        <input type="text" name="cantidad[]"
                            value="{{ $tipo == 'adecentamientos' ? $detalle->cantidad : $detalle->area }}"
                            class="form-control form-control-sm input-double-two-decimals cantidad-input">
                    </td>
                    <td class="precio-unitario-celda">
                        <input type="text" name="precio[]" value="{{ $detalle->precio_unitario }}"
                            class="form-control form-control-sm moneyDosDecimales precio-input">
                    </td>
                    <td class="costo-indirecto-celda">
                        <input type="text" name="indirecto[]" value="{{ $detalle->costo_indirecto }}"
                            class="form-control form-control-sm input-enteros costo-indirecto-input">
                    </td>
                    <td class="total_unitario total-celda">
                        {{ $detalle->total_format }}
                    </td>
                    <td class="align-middle table-actions">
                        <div class="action-buttons">
                            <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto"
                                id=""><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                    </td>
                </tr>
            @endforeach


            <tr id="tr-default" style="display:{{ $proforma->id ? 'none' : '' }}">
                <td colspan="7" class="text-center">No existen elementos en la
                    lista...</td>
            </tr>
        </tbody>
    </table>
</div>


<div class="row">
    <div class="col-sm-8 col-12">
        @if ($tipo == 'adecentamientos')
            @include('proformas.partials.notas_adecentamientos')
        @else
            @include('proformas.partials.incluye_planos')
        @endif
    </div>
    <div class="col-sm-4 col-12">
        <div class="row">
            <div class="col-sm-5">
                <label for="input_subtotal" class="col-form-label">SubTotal: </label>
            </div>

            <div class="col-sm-7">
                <label class="form-control" id="subtotal">$ {{ $proforma->subtotal_formatted ?? '$ 0.00' }}</label>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5">
                <div class="form-row p-0">
                    <div class="col-9">
                        <label class="col-form-label">descuento % </label>
                    </div>
                    <div class="col-3">
                        {{ Form::text('descuento', $proforma->descuento ?? '0', ['class' => 'form-control p-0 text-center', 'id' => 'porcentaje_descuento']) }}
                    </div>
                </div>
            </div>

            <div class="col-sm-7">
                <label class="form-control"
                    id="totales_descuento">{{ $proforma->total_descuento_formatted ?? '$ 0.00' }}
                </label>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                <div class="form-row p-0">
                    <div class="col-9">
                        <label class="col-form-label">iva % </label>
                    </div>
                    <div class="col-3">
                        {{ Form::text('porcentaje_iva', $proforma->iva ?? '15', ['class' => 'form-control p-0 text-center', 'id' => 'porcentaje_iva']) }}
                    </div>
                </div>
            </div>

            <div class="col-sm-7">
                <label class="form-control" id="totales_iva">{{ $proforma->total_iva_formatted ?? '$ 0.00' }}</label>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Total: </label>
            <div class="col-sm-7">
                <label class="form-control" id="total_proforma">{{ $proforma->total_formatted ?? '$ 0.00' }}</label>
            </div>
        </div>
    </div>
</div>

@if ($tipo == 'adecentamientos')
    @include('proformas.partials.termino_condiciones_adecentamientos')
@else
    @include('proformas.partials.plazos_pagos_planos')
@endif

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <h5>Observaciones</h5>
            {{ Form::textarea('observaciones', old('observaciones'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Ingrese observaciones adicionales']) }}
        </div>
    </div>


</div>
@section('scripts')
    <script src="{{ asset('js/proformas.js?v=' . config('app.version', '')) }}"></script>
@endsection
