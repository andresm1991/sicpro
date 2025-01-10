<div class="row">
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proyecto', ['class' => 'col-form-label']) }}
            <select name="proyecto" class="form-control select2-tag" data-placeholder="selecciona proyecto">
                <option></option>
                @foreach ($proyectos as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ isset($orden_pedido->proyecto_id) && $orden_pedido->proyecto_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('proyecto', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Tipo', ['class' => 'col-form-label']) }}
            <select name="etapa" class="form-control select2-tag" data-placeholder="selecciona opción">
                <option></option>
                @foreach ($etapa as $id => $nombre)
                    <option value="{{ $id }}" {{ $orden_pedido->etapa_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}</option>
                @endforeach
            </select>
            {!! $errors->first('etapa', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>

    <div class="col-md-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Tipo', ['class' => 'col-form-label']) }}
            <select name="actividad" class="form-control select2-tag" data-placeholder="selecciona etapa">
                <option></option>
                @foreach ($actividad as $id => $nombre)
                    <option value="{{ $id }}" {{ $orden_pedido->tipo_etapa_id == $id ? 'selected' : '' }}>
                        {{ $nombre }}</option>
                @endforeach
            </select>
            {!! $errors->first('actividad', '<small class="help-block text-danger error_mensajes">:message</small>') !!}
        </div>
    </div>
</div>

@section('scripts')
    <script src="{{ asset('js/adquisiciones_script.js') }}"></script>
@endsection
