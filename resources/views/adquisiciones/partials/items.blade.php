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
                {{ Form::text('cantidad', old('cantidad'), ['class' => 'form-control input-double', 'id' => 'cantidad']) }}
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>No es posible completar el pedido, por favor verifique que existan elementos agregados a la lista del pedido.</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

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
            @foreach ($orden_pedido->adquisiciones_detalle as $index => $element)
                <tr class="elementos-agregados">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $element->producto->descripcion }}</td>
                    <td class="edit-item">
                        <span>{{ $element->cantidad_solicitada }}</span>
                        <div class="d-flex align-items-center hidden">
                            <input type="numbre" class="form-control mr-2 input-double" name="cantidad[]"
                                value="{{ $element->cantidad_solicitada }}" step="0.01">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                    <td class="edit-item">
                        <span>{{ $element->necesidad }}</span>
                        <div class="d-flex align-items-center hidden">
                            <input type="text" class="form-control mr-2" name="necesidad[]"
                                value="{{ $element->necesidad }}">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                    <td class="align-middle table-actions">
                        <div class="action-buttons">
                            <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto"
                                id="">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </td>
                    <input type="hidden" name="productos[]" value="{{ $element->articulo_id }}">
                </tr>
            @endforeach
            <tr id="tr-default" style="display:{{ $orden_pedido->id ? 'none' : '' }}">
                <td colspan="5" class="text-center">No existen elementos en la lista...</td>
            </tr>

        </tbody>
    </table>
</div>
