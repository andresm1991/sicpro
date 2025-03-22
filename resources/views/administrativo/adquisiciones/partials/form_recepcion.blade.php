<div class="row">
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proyecto', ['class' => 'col-form-label']) }}
            {{ Form::label('', $adquisicion->proyecto_id > 0 ? $adquisicion->proyecto->nombre_proyecto : 'Otros', ['class' => 'form-control text-uppercase label-disabled']) }}
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Etapa', ['class' => 'col-form-label']) }}
            {{ Form::label('', $adquisicion->etapa->descripcion, ['class' => 'form-control text-uppercase label-disabled']) }}
        </div>
    </div>

    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Tipo', ['class' => 'col-form-label']) }}
            {{ Form::label('', $adquisicion->tipo_etapa->descripcion, ['class' => 'form-control text-uppercase label-disabled']) }}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            @if ($tipo == 'operativo')
                {{ Form::label('', $adquisicion->orden_recepcion->proveedor->razon_social, ['class' => 'form-control text-uppercase label-disabled text-truncate']) }}
            @else
                <select name="proveedor" class="form-control select2-basic-single"
                    data-placeholder="selecciona proveedor">
                    <option></option>
                    @foreach ($proveedores as $id => $nombre)
                        <option value="{{ $id }}"
                            {{ isset($adquisicion->orden_recepcion) && $adquisicion->orden_recepcion->proveedor_id == $id ? 'selected' : '' }}>
                            {{ $nombre }}</option>
                    @endforeach
                </select>
                {!! $errors->first('proveedor', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
            @endif
        </div>
    </div>

    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Numero Factura', ['class' => 'col-form-label']) }}
            {{ Form::text('numero_factura', $adquisicion->factura, ['class' => 'form-control', 'id' => 'nro_factura', 'placeholder' => 'Ingrese el número de factura']) }}
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Archivo', ['class' => 'col-form-label']) }}
            <div class="form-group form-search form-icon col-md-10 col-12  p-0">
                <i
                    class="fa-regular  {{ isset($adquisicion->archivo) ? 'fa-download' : '  fa-circle-exclamation' }} fa-lg form-control-icon"></i>


                @isset($adquisicion->archivo)
                    <a href="{{ doTemporaryUrl($adquisicion->archivo) }}" target="__blank"
                        class="form-control text-truncate text-decoration-none">
                        {{ substr($adquisicion->archivo, strrpos($adquisicion->archivo, '/') + 1) }}
                    </a>
                @else
                    <label for="archivo" id="archivo-label" class="form-control text-truncate ">Sin archivo cargado</label>
                @endisset

            </div>
        </div>
    </div>
    {{-- abrir modal para agregar nuevo producto al pedido  --}}
    <div class="col-12">
        <button type="button" class="btn btn-dark" data-toggle="modal" data-backdrop="static" data-keyboard="false"
            data-target="#agregarProductosModal">Agregar producto</button>
    </div>
</div>
