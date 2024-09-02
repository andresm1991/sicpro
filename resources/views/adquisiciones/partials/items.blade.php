<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-sm-5">
            <div class="form-group">
                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                <select name="productos" id="productos" class="form-control">
                    <option></option>
                    @foreach ($productos as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                {{ Form::text('cantidad', old('cantidad'), ['class' => 'form-control solo-numeros', 'id' => 'cantidad']) }}
            </div>
        </div>
        <div class="col-sm-5">
            <div class="form-group">
                {{ Form::label('', 'Necesidad', ['class' => 'col-form-label']) }}
                {{ Form::text('necesidad', old('necesidad'), ['class' => 'form-control', 'id' => 'necesidad']) }}
            </div>
        </div>
    </div>


    <div class="form-group">
        <button type="button" class="btn btn-dark" id="add-producto-adquisicion">Agregar</button>
    </div>
</fieldset>



<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Necesidad</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            <tr id="tr-default">
                <td colspan="5" class="text-center">No existen elementos en la lista...</td>
            </tr>
        </tbody>
    </table>
</div>
