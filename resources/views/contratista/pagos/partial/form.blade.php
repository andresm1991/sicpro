<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Pago Nro    ', ['class' => 'col-form-label ']) }}
            <label for="" class="form-control text-uppercase label-disabled " >{{ numeroOrden($orden_trabajo->numero_pago_contratista) }}</label>
        </div>
    </div>
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Contratista', ['class' => 'col-form-label']) }}
            <label for="" class="form-control text-uppercase label-disabled" >{{ $orden_trabajo->proveedor->razon_social }}</label>
        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
            <label for="" class="form-control text-uppercase label-disabled" >{{ $orden_trabajo->articulo->descripcion }}</label>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('', 'Tipo', ['class' => 'col-form-label']) }}
            <select name="tipo" class="form-control select2-basic-single"
                data-placeholder="Selecione opción">
                <option></option>
                <option value="AVANCE"> Avance</option>
                <option value="LIQUIDACION"> Liquidación</option>
            </select>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('', 'Forma de pago', ['class' => 'col-form-label']) }}
            <select name="forma_pago" class="form-control select2-basic-single"
                data-placeholder="Selecione opción">
                <option></option>
                <option value="EFECTIVO"> Efectivo</option>
                <option value="TRANSFERENCIA"> Transferencia</option>
                <option value="CHEQUE"> Cheque</option>
            </select>
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            {{ Form::label('', 'Valor', ['class' => 'col-form-label']) }}
            {{ Form::text('valor', old('valor'), ['class' => 'form-control input-double', 'id' => 'valor' ,'placeholder' => '$ 0,00']) }}
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            {{ Form::label('', 'Saldo', ['class' => 'col-form-label']) }}
            {{ Form::text('saldo', number_format(($orden_trabajo->total_contratistas - $orden_trabajo->pagos_contratistas),2), ['class' => 'form-control input-double', 'id' => 'saldo', 'placeholder' => '$ 0,00', 'disabled']) }}
        </div>
    </div>

    <div class="col-sm-12">
        <div class="form-group">
            {{ Form::label('', 'Detalle', ['class' => 'col-form-label']) }}
            {{ Form::textarea('detalle', old('detalle'), ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Ingresar detalle del pago']) }}
        </div>
    </div>
</div>

@section('scripts')
    <script>
        var url =
            "{{ route('proyecto.adquisiciones.contratista', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id]) }}";
    </script>
    <script src="{{ asset('js/orden_trabajo_contratistas_scripts.js') }}" type="module"></script>
@endsection