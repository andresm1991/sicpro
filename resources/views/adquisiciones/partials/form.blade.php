<div class="row">
    <div class="col-md-6 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proyecto', ['class' => 'col-form-label']) }}
            {{ Form::text('proyecto', $proyecto->nombre_proyecto, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
        </div>

        <div class="form-group">
            {{ Form::label('', 'Etapa', ['class' => 'col-form-label']) }}
            {{ Form::text('etapa', $tipo_adquisicion->descripcion, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            <select name="proveedor" class="form-control select2-basic-single col-12"
                data-placeholder="Selecione proveedor"
                {{ isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '' }}>
                <option></option>
                @foreach ($proveedores as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ old('proveedor') == $id || (isset($orden_pedido->orden_recepcion->proveedor_id) && $orden_pedido->orden_recepcion->proveedor_id == $id) ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('proveedor', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>

        <div class="form-group">
            {{ Form::label('', 'Forma de Pago', ['class' => 'col-form-label']) }}
            <div class="select_wrapper">
                @forelse ($forma_pagos as $key => $value)
                    <label class="rounded-0 text-white">
                        <input type="radio" name="forma_pago" class="d-none" value="{{ $key }}"
                            {{ old('forma_pago') == $key || (isset($orden_pedido->orden_recepcion->forma_pago->id) && $orden_pedido->orden_recepcion->forma_pago->id == $key) ? 'checked' : '' }}
                            {{ isset($orden_pedido->orden_recepcion) && !$orden_pedido->orden_recepcion->editar ? 'disabled' : '' }}>
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

@include('adquisiciones.partials.items')

@section('scripts')
    <script src="{{ asset('js/adquisiciones_script.js') }}"></script>
@endsection
