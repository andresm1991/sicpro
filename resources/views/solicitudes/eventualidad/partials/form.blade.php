<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            <label class="col-form-label">Colaborador <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            {{ Form::select('users[]', $users, old('users', $solicitud->usuariosEventualidad->pluck('usuario_id')->toArray()), ['class' => 'select2-basic-multiple', 'multiple' => 'multiple', 'data-placeholder' => 'Selecione opción']) }}

            {!! $errors->first('users', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
        </div>

        @can('solicitudes.editar')
            <div class="form-group">
                <label class="col-form-label">Estado Solicitud <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                {{ Form::select('estado_solicitud', $estados_solicitud->prepend('', ''), old('estado_solicitud', $solicitud->estado_id), ['class' => 'select2-basic-single', 'data-placeholder' => 'Selecione opción']) }}

                {!! $errors->first('estado_solicitud', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
            </div>
        @endcan

    </div>
    <div class="col-sm-8 col-12">
        <div class="form-group">
            <label class="col-form-label">Detalle <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            {{ Form::textarea('detalle', old('detalle', $solicitud->detalle), ['class' => 'form-control', 'placeholder' => 'Ingresa el detalle de la eventualidad.', 'rows' => 5]) }}
            {!! $errors->first('detalle', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
        </div>
    </div>

</div>
@section('scripts')
    <script src="{{ asset('js/solicitud_scripts.js') }}"></script>
@endsection
