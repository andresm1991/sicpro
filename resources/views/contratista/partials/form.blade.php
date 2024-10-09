<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            @if ($orden_trabajo->proveedor_id)
                {{ Form::label('',  $orden_trabajo->proveedor->razon_social, ['class' => 'form-control label-disabled text-uppercase']) }}
            @else
                <select name="proveedor" id="proveedor" class="form-control select2-basic-single"
                    data-placeholder="Selecione proveedor">
                    <option></option>
                    @foreach ($proveedores as $id => $nombre)
                        <option value="{{ $id }}" {{ $orden_trabajo->proveedor_id == $id ? 'selected':'' }}>{{ $nombre }}</option>
                    @endforeach
                </select>    
            @endif
            
        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
            @if ($orden_trabajo->proveedor_id)
                {{ Form::label('',  $orden_trabajo->articulo->descripcion, ['class' => 'form-control label-disabled text-uppercase']) }}
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
</div>

<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-md-4 col 12">
            <div class="form-group"> 
            {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                <select name="producto" id="producto" class="form-control select2-basic-single" data-placeholder="Selecione producto">
                    <option></option>
                    @foreach ($articulos as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-2 col-md-2 col-12">
            <div class="form-group"> 
                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                <input type="text" name="cantidad" class="form-control input-double" placeholder="0" />
            </div>
        </div>

        <div class="col-sm-2 col-md-2 col-12">
            <div class="form-group"> 
                {{ Form::label('', 'Unidad medida', ['class' => 'col-form-label']) }}
                <select name="unidad-medida" id="unidad-medida" class="form-control select2-basic-single" data-placeholder="Selecione opción">
                    <option></option>
                    @foreach ($unidades_medidas as $id => $medida)
                        <option value="{{ $medida->id }}">{{ $medida->descripcion }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-2 col-md-2 col-12">
            <div class="form-group"> 
                {{ Form::label('', 'Precio unitario', ['class' => 'col-form-label']) }}
                <input type="text" name="precio-unitario" class="form-control input-double" placeholder="$0,00" />
            </div>
        </div>
        <div class="col-sm-12 col-md-12 col-12">
            <div class="form-group"> 
                <button type="button" class="btn btn-dark" id="agregar-producto">Agregar producto</button>
            </div>
            
        </div>
    </div>
</fieldset>

<div class="table-responsive">
    <table class="table table-bordered table-hover" id="tabla-planificacion">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad</th> 
                <th scope="col">Unidad</th>
                <th scope="col">Precio Unitario</th>
                <th scope="col">Total</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orden_trabajo->detalle_contratistas as $index => $detalle)
                <tr class="elementos-agregados">
                    <td>{{ $index +1 }}</td>
                    <td>
                        {{ $detalle->articulo->descripcion }}
                        <input type="hidden" name="productos[]" value="{{ $detalle->articulo->id }}">
                    </td>
                    <td class="edit-item">
                        <span>{{ $detalle->cantidad }}</span>
                        <div class="d-flex align-items-center hidden">
                            <input type="text" class="form-control mr-2 input-double" name="cantidad[]"
                                value="{{ $detalle->cantidad }}">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                    <td>
                        <span>{{ $detalle->unidad_medida->descripcion }}</span>
                        {{ Form::hidden('unidad_medida[]', $detalle->unidad_medida_id) }}
                    </td>
                    <td class="edit-item">
                        <span>$ {{ number_format($detalle->valor_unitario, 2) }}</span>
                        <div class="d-flex align-items-center hidden">
                            <input type="text" class="form-control mr-2 input-double" name="precio_unitario[]"
                                value="{{ $detalle->valor_unitario }}">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                    <td><span class="total">{{ number_format(($detalle->valor_unitario * $detalle->cantidad), 2) }}</span></td>
                    <td class="align-middle table-actions">
                        <div class="action-buttons">
                            <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto" id=""><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                    </td>
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

@section('scripts')
    <script>
        var url =
            "{{ route('proyecto.adquisiciones.contratista', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id]) }}";
    </script>
    <script src="{{ asset('js/orden_trabajo_contratistas_scripts.js') }}" type="module"></script>
@endsection
