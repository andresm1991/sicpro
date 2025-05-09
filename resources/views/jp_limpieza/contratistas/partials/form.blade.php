<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            @if ($contratista->proveedor_id)
                {{ Form::label('', $contratista->proveedor->razon_social, ['class' => 'form-control label-disabled text-uppercase']) }}
            @else
                {{ Form::select('proveedor', $proveedores, $contratista->proveedor_id, ['class' => 'form-control select2-basic-single', 'id' => 'proveedor', 'data-placeholder' => 'Selecione proveedor']) }}
            @endif

        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
            @if ($contratista->proveedor_id)
                {{ Form::label('', $contratista->categoria->descripcion, ['class' => 'form-control label-disabled text-uppercase']) }}
            @else
                <select name="categoria" id="categoria" class="form-control select2-basic-single"
                    data-placeholder="Selecione categoría">
                    <option></option>

                </select>
            @endif
        </div>
    </div>

    <div class="col-sm-2 col-12">
        <div class="form-group">
            {{ Form::label('', 'Plazo', ['class' => 'col-form-label']) }}
            {{ Form::text('plazo', old('plazo', $contratista->plazo), ['class' => 'form-control input-enteros', 'placeholder' => '0']) }}
        </div>
    </div>
</div>
