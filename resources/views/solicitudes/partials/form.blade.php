<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            <label class="col-form-label">Colaborador <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            <select name="user" id="" class="select2-basic-single" data-placeholder="Selecione colaborador">
                <option value=""></option>
                @foreach ($users as $index => $nombre)
                    <option value="{{ $index }}">{{ $nombre }}</option>
                @endforeach
            </select>

            {!! $errors->first('user', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            <label class="col-form-label">Tipo Solicitud <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            <select name="tipo_solicitud" class="select2-basic-single" data-placeholder="Selecione opción">
                <option value=""></option>
                @foreach ($tipo_solicitudes as $index => $nombre)
                    <option value="{{ $index }}">{{ $nombre }}</option>
                @endforeach
            </select>

            {!! $errors->first('tipo_solicitud', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            <label class="col-form-label">Estado Solicitud <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            <select name="estado_solicitud" class="select2-basic-single" data-placeholder="Selecione opción">
                <option value=""></option>
                @foreach ($estados_solicitud as $index => $nombre)
                    <option value="{{ $index }}">{{ $nombre }}</option>
                @endforeach
            </select>

            {!! $errors->first('estado_solicitud', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            <label class="col-form-label">Fecha Desde <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            {{ Form::text('fecha_desde', old('fecha_desde', $solicitud->fecha_desde ?? now()->format('d-m-Y')), ['class' => 'form-control datepicker-no-back', 'placeholder' => 'DD-MM-YYYY']) }}
            {!! $errors->first('fecha_desde', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            <label class="col-form-label">Fecha Hasta <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            {{ Form::text('fecha_hasta', old('fecha_hasta', $solicitud->fecha_hasta ?? now()->format('d-m-Y')), ['class' => 'form-control datepicker-no-back', 'placeholder' => 'DD-MM-YYYY']) }}
            {!! $errors->first('fecha_hasta', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            <label class="col-form-label">Hora Desde <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            {{ Form::text('hora_inicio', old('hora_inicio', $solicitud->hora_desde), ['class' => 'form-control timepicker', 'id' => 'hora_inicio','placeholder' => 'HH:mm']) }}
            {!! $errors->first('hora_inicio', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            <label class="col-form-label">Hora Hasta <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            {{ Form::text('hora_fin', old('hora_fin', $solicitud->hora_hasta), ['class' => 'form-control timepicker', 'id' => 'hora_fin', 'placeholder' => 'HH:mm']) }}
            {!! $errors->first('hora_fin', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            <label class="col-form-label">Tiempo total</label><br>
            <span id="resultado" class="form-control">00:00</span>
          </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label class="col-form-label">Detalle Solicitud <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            {{ Form::textarea('detalle', old('detalle', $solicitud->detalle), ['class' => 'form-control', 'placeholder' => 'Ingresa el detalle de la solicitud.', 'rows' => 3]) }}
            {!! $errors->first('detalle', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
          </div>
    </div>
</div>

  <div class="form-check">
    <input name="recuperable" type="checkbox" class="form-check-input" id="check_recuperable" value="true">
    <label class="form-check-label" for="check_recuperable">Recuperable</label>
  </div>


  @section('scripts')
    <script src="{{ asset('js/solicitud_scripts.js') }}"></script>
@endsection
