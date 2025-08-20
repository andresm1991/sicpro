<div class="row">
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proyecto', ['class' => 'col-form-label']) }}
            <select name="proyecto" class="form-control select2-basic-single" data-placeholder="selecciona proyecto">
                <option></option>
                @foreach ($proyectos as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ isset($adquisicion->proyecto_id) && $adquisicion->proyecto_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('proyecto', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Etapa', ['class' => 'col-form-label']) }}
            <select name="etapa" class="form-control select2-basic-single" data-placeholder="selecciona opción">
                <option></option>
                @foreach ($etapa as $id => $nombre)
                    <option value="{{ $id }}" {{ $adquisicion->etapa_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}</option>
                @endforeach
            </select>
            {!! $errors->first('etapa', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>

    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Tipo', ['class' => 'col-form-label']) }}
            <select name="actividad" class="form-control select2-basic-single" data-placeholder="selecciona etapa">
                <option></option>
                @foreach ($actividad as $id => $nombre)
                    <option value="{{ $id }}" {{ $adquisicion->tipo_etapa_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}</option>
                @endforeach
            </select>
            {!! $errors->first('actividad', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            <select name="proveedor" class="form-control select2-basic-single" data-placeholder="selecciona proveedor">
                <option></option>
                @foreach ($proveedores as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ isset($adquisicion->orden_recepcion) && $adquisicion->orden_recepcion->proveedor_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}</option>
                @endforeach
            </select>
            {!! $errors->first('proveedor', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>

    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Numero Factura', ['class' => 'col-form-label']) }}
            {{ Form::text('numero_factura', $adquisicion->factura, ['class' => 'form-control', 'id' => 'nro_factura', 'placeholder' => 'Ingrese el número de factura']) }}
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

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'Unidad Media', ['class' => 'col-form-label']) }}
                <select name="unidad_medida" id="unidad_medida" class="form-control select2-tag"
                    data-placeholder="selecciona opción">
                    <option></option>
                    @foreach ($unidad_medidas as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'Valor Unitario', ['class' => 'col-form-label']) }}
                {{ Form::text('valor_unitario', 0, ['class' => 'form-control money', 'id' => 'valor_unitario']) }}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'IVA %', ['class' => 'col-form-label']) }}
                {{ Form::text('iva', 0, ['class' => 'form-control input-enteros', 'id' => 'iva']) }}
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

    {{ Form::hidden('slug_adquisicion', isset($tipo_etapa->slug) ? strtoupper($tipo_etapa->slug) : '') }}

    @if (isset($tipo_etapa->slug) && strtoupper($tipo_etapa->slug) == 'SERVICIOS')
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group">
                    {{ Form::label('', 'Unidad de Medida', ['class' => 'col-form-label']) }}
                    <select name="unidad" id="unidad_medida" class="form-control" data-placeholder="selecciona opción">
                        <option></option>
                        @foreach ($unidad_medidas as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-2">
                <div class="form-group">
                    {{ Form::label('', 'Precio', ['class' => 'col-form-label']) }}
                    {{ Form::text('precio_unitario', old('precio_unitario', $orden_pedido->precio), ['class' => 'form-control currency']) }}
                </div>
            </div>
        </div>
    @endif


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


@section('scripts')
    <script src="{{ asset('js/administrativo_scripts.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection
