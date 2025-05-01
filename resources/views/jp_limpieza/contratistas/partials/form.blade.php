<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            {{ Form::select('proveedor', $proveedores, $contratista->proveedor_id, ['class' => 'form-control select2-basic-single', 'id' => 'proveedor', 'data-placeholder' => 'Selecione proveedor']) }}
            {{-- 
            @if ($orden_trabajo->proveedor_id)
                {{ Form::label('', $orden_trabajo->proveedor->razon_social, ['class' => 'form-control label-disabled text-uppercase']) }}
            @else
                <select name="proveedor" id="proveedor" class="form-control select2-basic-single"
                    data-placeholder="Selecione proveedor">
                    <option></option>
                    @foreach ($proveedores as $id => $nombre)
                        <option value="{{ $id }}" {{ $orden_trabajo->proveedor_id == $id ? 'selected' : '' }}>
                            {{ $nombre }}</option>
                    @endforeach
                </select>
            @endif
 --}}
        </div>
    </div>
    {{-- 
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
            @if ($orden_trabajo->proveedor_id)
                {{ Form::label('', $orden_trabajo->articulo->descripcion, ['class' => 'form-control label-disabled text-uppercase']) }}
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
            {{ Form::label('', 'Plazo semanas', ['class' => 'col-form-label']) }}
            {{ Form::text('plazo_semanas', old('plazo_semanas'), ['class' => 'form-control input-enteros']) }}
        </div>
    </div>
     --}}
</div>
