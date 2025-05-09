<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Pago Nro    ', ['class' => 'col-form-label ']) }}
            <label for="" class="form-control text-uppercase label-disabled ">{{ numeroOrden($pago) }}</label>
        </div>
    </div>
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Contratista', ['class' => 'col-form-label']) }}
            <label for=""
                class="form-control text-uppercase label-disabled">{{ $contratista->proveedor->razon_social }}</label>
        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
            <label for=""
                class="form-control text-uppercase label-disabled">{{ $contratista->categoria->descripcion }}</label>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('', 'Tipo', ['class' => 'col-form-label']) }}
            {{ Form::select('tipo_pago', ['' => '', 'AVANCE' => 'AVANCE', 'LIQUIDACION' => 'LIQUIDACION'], $pago->tipo_pago, ['class' => 'form-control', 'id' => 'tipo', 'data-placeholder' => 'Selecione opción']) }}
            {!! $errors->first('tipo_pago', '<small class="help-block text-danger">:message</small>') !!}
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('', 'Forma de pago', ['class' => 'col-form-label']) }}
            {{ Form::select('forma_pago', getTipoPagos(true), $pago->forma_pago_id, ['class' => 'form-control select2-tag', 'id' => 'forma_pago', 'data-placeholder' => 'Selecione opción']) }}

            {!! $errors->first('forma_pago', '<small class="help-block text-danger">:message</small>') !!}
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            {{ Form::label('', 'Valor', ['class' => 'col-form-label']) }}
            {{ Form::text('monto', old('monto', $pago->monto), ['class' => 'form-control money', 'id' => 'monto', 'placeholder' => '$ 0.0000']) }}
            {!! $errors->first('monto', '<small class="help-block text-danger">:message</small>') !!}
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            {{ Form::label('', 'Saldo', ['class' => 'col-form-label']) }}
            {{ Form::text('saldo', $contratista->total_pagos_registrados_formatted, ['class' => 'form-control money', 'id' => 'saldo', 'placeholder' => '$ 0.0000', 'disabled']) }}
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('', 'Estado', ['class' => 'col-form-label']) }}
            {{ Form::select('estado', getEstadosPagos(true), $pago->estado_id, ['class' => 'form-control', 'id' => 'estado', 'data-placeholder' => 'Selecione opción']) }}
            {!! $errors->first('estado', '<small class="help-block text-danger">:message</small>') !!}
        </div>
    </div>

    <div class="col-sm-12">
        <div class="form-group">
            {{ Form::label('', 'Detalle', ['class' => 'col-form-label']) }}
            {{ Form::textarea('detalle', old('detalle', $pago->observaciones), ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Ingresar detalle del pago']) }}
            {!! $errors->first('detalle', '<small class="help-block text-danger">:message</small>') !!}
        </div>
    </div>
</div>
