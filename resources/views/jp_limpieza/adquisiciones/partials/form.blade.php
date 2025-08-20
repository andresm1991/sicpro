<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group form-search form-icon">
            {{ Form::label('', 'fecha de adquisición', ['class' => 'col-form-label']) }}
            <i class="fa-regular fa-calendar-days fa-lg form-control-icon"></i>
            {{ Form::text('fecha', old('fecha', $adquisicion->fecha_formateada ?? date('Y-m-d')), ['class' => 'form-control form-control-round datepicker-2', 'id' => 'fecha', 'readonly' => true]) }}
            <small class="form-text text-primary">Click en el campo para cambiar la fecha de adquisición.</small>
        </div>
    </div>
</div>

<div class="row">
    @isset($proyecto)
        {{ Form::hidden('', $tipoAdquisicion->slug, ['id' => 'tipo_adquisicion']) }}

        <div class="col-sm-6 col-12">
            <div class="form-group">
                {{ Form::label('', 'Proyecto', ['class' => 'col-form-label']) }}
                {{ Form::text('', $proyecto->nombre_proyecto, ['class' => 'form-control', 'disabled' => true]) }}
                {{ Form::hidden('proyecto', $proyecto->id) }}
            </div>
        </div>

        <div class="col-sm-6 col-12">
            <div class="form-group">
                {{ Form::label('', 'entidad', ['class' => 'col-form-label']) }}
                {{ Form::text('', $proyecto->entidad, ['class' => 'form-control', 'disabled' => true]) }}
            </div>
        </div>
    @else
        <div class="col-sm-6 col-12">
            <div class="form-group">
                {{ Form::label('', 'Tipo adquisición', ['class' => 'col-form-label']) }}
                {{ Form::select('tipo_adquisicion', $tipoAdquisiciones, $adquisicion->tipo_id, ['class' => 'form-control', 'id' => 'tipo-adquisicion', 'data-placeholder' => 'selecciona o agrega la necesidad']) }}
            </div>
        </div>
    @endisset


    <div class="col-md-6 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            <select name="proveedor" id="proveedor" class="form-control" data-placeholder="Selecione proveedor">
                <option></option>
                @foreach (getProveedores(true) as $id => $nombre)
                    <option value="{{ $id }}" {{ $adquisicion->proveedor_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('proveedor', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>

    <div class="col-md-3 col-12">
        <div class="form-group">
            {{ Form::label('', 'Nro. Factura', ['class' => 'col-form-label']) }}
            {{ Form::text('factura', $adquisicion->factura, ['class' => 'form-control', 'id' => 'nro_factura', 'placeholder' => 'Ingrese el número de factura']) }}
            {!! $errors->first('factura', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>

    <div class="col-sm-3 col-12">
        <div class="form-group">
            {{ Form::label('', 'Archivo', ['class' => 'col-form-label']) }}

            <div class="form-group form-search form-icon col-md-10 col-12  p-0">
                <i class="fa-solid fa-upload fa-lg form-control-icon"></i>
                <input type="file" name="archivo" class="d-none" id="archivo">
                <label for="archivo" id="archivo-label" class="form-control text-truncate">Seleccionar
                    Archivo...</label>

                @isset($adquisicion->archivo)
                    <a href="{{ doTemporaryUrl($adquisicion->archivo) }}" target="__blank" class="badge badge-secondary">
                        {{ substr($adquisicion->archivo, strrpos($adquisicion->archivo, '/') + 1) }} <i
                            class="fa-solid fa-download"></i>
                    </a>
                @endisset

            </div>

            {!! $errors->first('archivo', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            {{ Form::label('', 'Forma de Pago', ['class' => 'col-form-label']) }}
            <div class="select_wrapper">
                @forelse (formasPagos() as $key => $value)
                    <label class="rounded-0 text-white">
                        <input type="radio" name="forma_pago" class="d-none" id="forma-pago"
                            value="{{ $key }}"
                            {{ old('forma_pago') == $key || (isset($adquisicion->forma_pago_id) && $adquisicion->forma_pago_id == $key) ? 'checked' : '' }}
                            {{ !auth()->user()->hasRole(['Administrador', 'Gerencial']) && $adquisicion->estado == 'completado'? 'disabled': '' }}>
                        <span class="text-center d-block py-3">{{ $value }}</span>
                    </label>
                @empty
                    <small class="help-block text-danger error_mensajes">No existen formas de pagos
                        registrados.</small>
                @endforelse
            </div>
            {!! $errors->first('forma_pago', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>
</div>
