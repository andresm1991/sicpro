{{ Form::hidden('tipo_proforma', $tipo) }}
<div class="form-row">
    <div class="form-group col-md-2 col-12">
        <label class="col-form-label">emisición</label>
        {{ Form::text('fecha', old('fecha', $proforma->fecha ?? date('d-m-Y')), ['class' => 'form-control datepicker']) }}
    </div>

    <div class="form-group col-md-10 col-12">
        <label class="col-form-label">Cliente</label>
        {{ Form::select('cliente', getClientes(), null, ['class' => 'form-control select2', 'data-placeholder' => 'Seleccione un cliente']) }}
    </div>
</div>
