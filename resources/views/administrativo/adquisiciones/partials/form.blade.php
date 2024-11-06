<div class="row">
    <div class="col-md-7 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Proyecto', ['class' => 'col-sm-2 col-form-label']) }}
            <div class="col-sm-10">
                <select name="proyecto" class="form-control select2-tag"
                    data-placeholder="selecciona proyecto">
                    @foreach ($proyectos as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-5 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Etapa', ['class' => 'col-sm-2 col-form-label']) }}
            <div class="col-sm-10">
                <select name="etapa" class="form-control select2-tag"
                    data-placeholder="selecciona etapa">
                    @foreach ($proyectos as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-sm-5">
            <div class="form-group">
                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                <select name="productos" id="productos" class="form-control select2-tag"
                    data-placeholder="selecciona o agrega el producto">
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
                <select name="necesidad" id="necesidad" class="form-control select2-tag"
                    data-placeholder="selecciona o agrega la necesidad">
                    <option></option>
                    @foreach (palabras() as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>


    <div class="form-group">
        <button type="button" class="btn btn-dark" id="add-producto-adquisicion">Agregar</button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>No es posible completar el pedido, por favor verifique que existan elementos agregados a la lista
                del pedido.</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

</fieldset>