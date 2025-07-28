{{ Form::hidden('tipo_proforma', $tipo, ['id' => 'tipo-proforma']) }}
<div class="form-row">
    <div class="form-group col-md-2 col-12">
        <label class="col-form-label">emisición</label>
        {{ Form::text('fecha', old('fecha', $proforma->fecha_formatted ?? date('d-m-Y')), ['class' => 'form-control datepicker', 'id' => 'fecha']) }}
    </div>

    <div class="form-group col-md-5 col-12">
        <label class="col-form-label">Cliente</label>
        {{ Form::select('cliente', getClientes(), $proforma->cliente_id ?? null, ['class' => 'form-control select2', 'id' => 'cliente', 'data-placeholder' => 'Seleccione un cliente']) }}
    </div>
    @if ($tipo == 'diseno_planos')
        <div class="form-group col-md-5 col-12">
            <label class="col-form-label">Ubicación del lote</label>
            {{ Form::text('ubicacion_lote', $proforma->ubicacion_lote, ['class' => 'form-control', 'id' => 'ubicacion_lote', 'placeholder' => 'Ingrese ubicación']) }}
        </div>

        <div class="form-group col-md-2 col-12">
            <label class="col-form-label">Área del lote</label>
            {{ Form::text('area_lote', $proforma->area_lote, ['class' => 'form-control input-enteros', 'id' => 'area_lote', 'placeholder' => 'Ingrese área']) }}
        </div>

        <div class="form-group col-md-4 col-12">
            <label class="col-form-label">presupuesto para construccion de vivienda</label>
            {{ Form::text('presupuesto', $proforma->presupuesto, ['class' => 'form-control moneyDosDecimales', 'id' => 'presupuesto', 'placeholder' => '$ 0.00']) }}
        </div>
    @endif



</div>
