<div class="row">
    <div class="col-md-7 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Fecha', ['class' => 'col-sm-2 col-form-label']) }}
            <div class="col-sm-4">
                {{ Form::label('', date('Y-m-d'), ['class' => 'form-control text-capitalize', 'readonly']) }}
            </div>
        </div>
    </div>
    <div class="col-md-5 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Etapa', ['class' => 'col-sm-3 col-form-label']) }}
            <div class="col-sm-9">
                {{ Form::text('etapa', $tipo_adquisicion->descripcion, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-7 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Proyecto', ['class' => 'col-sm-2 col-form-label']) }}
            <div class="col-sm-10">
                {{ Form::text('proyecto', $proyecto->nombre_proyecto, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
            </div>
        </div>
    </div>
    <div class="col-md-5 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Proveedor', ['class' => 'col-sm-3 col-form-label']) }}
            <div class="col-sm-9">
                <select name="proveedor" class="form-control select2-basic-single"
                    data-placeholder="Selecione proveedor"
                    {{ isset($orden_recepcion) && !$orden_recepcion->editar ? 'disabled' : '' }}>
                    <option></option>
                    @foreach ($proveedores as $id => $nombre)
                        <option value="{{ $id }}"
                            {{ old('proveedor') == $id || (isset($orden_recepcion->proveedor_id) && $orden_recepcion->proveedor_id == $id) ? 'selected' : '' }}>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
                {!! $errors->first('proveedor', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script src="{{ asset('js/adquisiciones_script.js') }}"></script>
@endsection
