<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                {{ Form::select('', getProdutosProformas(), '', ['class' => 'form-control select2-tag', 'data-placeholder' => 'selecciona producto', 'id' => 'producto']) }}
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                {{ Form::text('', 0, ['class' => 'form-control input-double', 'id' => 'cantidad']) }}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'V.Unit', ['class' => 'col-form-label']) }}
                {{ Form::text('', 0, ['class' => 'form-control money', 'id' => 'valor_unitario']) }}
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'iva', ['class' => 'col-form-label']) }}
                {{ Form::text('', 0, ['class' => 'form-control input-enteros', 'id' => 'iva']) }}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'total', ['class' => 'col-form-label']) }}
                {{ Form::text('', '', ['class' => 'form-control money', 'id' => 'total', 'placeholder' => '$ 0.0000', 'disabled' => true]) }}
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
                <th scope="col">Cantidad</th>
                <th scope="col">V.Unit</th>
                <th scope="col">% iva</th>
                <th scope="col">total</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>


<div class="row">
    <div class="col-sm-8 col-12">
        <div class="form-group">
            <label class="col-form-label">Notas</label>
            {{ Form::textarea(
                'nota',
                old(
                    'nota',
                    $proforma->nota ??
                        'Los trabajos incluyen todo el equipamiento de seguridad requerido, así como la seguridad social del personal que ingrese a trabajar.',
                ),
                ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Ingrese una nota para la proforma'],
            ) }}
        </div>
    </div>
    <div class="col-sm-4 col-12">
        <div class="row">
            <div class="col-sm-5">
                <label for="input_subtotal" class="col-form-label">SubTotal: </label>
            </div>

            <div class="col-sm-7">
                <label class="form-control" id="subtotal">$ 0.00</label>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5">
                <div class="form-row p-0">
                    <div class="col-9">
                        <label class="col-form-label">descuento % </label>
                    </div>
                    <div class="col-3">
                        {{ Form::text('descuento', old('descuento', $proforma->descuento ?? '0'), ['class' => 'form-control p-0 text-center', 'id' => 'porcentaje_descuento']) }}
                    </div>
                </div>
            </div>

            <div class="col-sm-7">
                <label class="form-control" id="totales_descuento">$ 0.00</label>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                <div class="form-row p-0">
                    <div class="col-9">
                        <label class="col-form-label">iva % </label>
                    </div>
                    <div class="col-3">
                        {{ Form::text('porcentaje_iva', old('iva', $proforma->iva ?? '15'), ['class' => 'form-control p-0 text-center', 'id' => 'porcentaje_iva']) }}
                    </div>
                </div>
            </div>

            <div class="col-sm-7">
                <label class="form-control" id="totales_iva">$ 0.00</label>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 col-form-label">Total: </label>
            <div class="col-sm-7">
                <label class="form-control" id="total_proforma">$ 0.00</label>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <h4>terminos y condiciones</h4>
        </div>
    </div>

    <div class="col-sm-12 form-group">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <h6 class="mt-2">Validez de la proforma: </h6>
            </div>
            <div class="col-auto">
                {{ Form::text('validez', old('validez', $proforma->validez ?? '15 días calendario'), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>

    <div class="col-sm-12 form-group">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <h6 class="mt-2">forma de pago: </h6>
            </div>
            <div class="col-auto">
                {{ Form::text('forma_pago', old('forma_pago', $proforma->forma_pago ?? 'Contado'), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>

    <div class="col-sm-12 form-group">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <h6 class="mt-2">Tiempo de entrega: </h6>
            </div>
            <div class="col-auto">
                {{ Form::text('plazo_entrega', old('plazo_entrega', $proforma->plazo_entrega ?? '15 días laborables'), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <h5>Observaciones</h5>
            {{ Form::textarea('observaciones', old('observaciones'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Ingrese observaciones adicionales']) }}
        </div>
    </div>


</div>
@section('scripts')
    <script src="{{ asset('js/proformas.js') }}"></script>
@endsection
