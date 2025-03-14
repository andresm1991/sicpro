<div class="row">
    <div class="col-sm-6 col-12">
        <div class="form-group">
            <label class="col-form-label">Colaborador <i class="fa-regular fa-asterisk fa-2xs"></i></label>
            @if (isset($users))
                <select name="user" id="" class="select2-basic-single" data-placeholder="Selecione colaborador">
                    <option value=""></option>
                    @foreach ($users as $index => $nombre)
                        <option value="{{ $index }}">{{ $nombre }}</option>
                    @endforeach
                </select>
                {!! $errors->first('user', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
            @else
                <input type="text" class="form-control" value="{{ $solicitud->usuario->nombre }}" disabled>
            @endif
        </div>
        <div class="form-group row">
            <label class="col-sm-auto">Tiempo pendiente de reposición: </label>
            <div class="col-sm-auto p-0">
                <input type="text" readonly class="form-control-plaintext text-uppercase p-0" id="tiempo_pendiente"
                    value="0 horas y 0 minutos">
            </div>
        </div>
    </div>


    <div class="col-sm-6 col-12">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="col-form-label">Fecha de reposición <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('fecha', old('fecha', $reposicion->fecha ? \Carbon\Carbon::createFromFormat('Y-m-d', $reposicion->fecha)->format('d-m-Y') : now()->format('d-m-Y')), ['class' => 'form-control datepicker-no-back', 'placeholder' => 'DD-MM-YYYY']) }}
                    {!! $errors->first('fecha', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label class="col-form-label">Hora Desde <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('hora_inicio', old('hora_inicio', $reposicion->hora_desde ? Str::substr($reposicion->hora_desde, 0, 5) : '08:00'), ['class' => 'form-control timepicker', 'id' => 'hora_inicio', 'placeholder' => '0:00']) }}
                    {!! $errors->first('hora_inicio', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="col-form-label">Hora Hasta <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('hora_fin', old('hora_fin', $reposicion->hora_hasta ? Str::substr($reposicion->hora_hasta, 0, 5) : '08:00'), ['class' => 'form-control timepicker', 'id' => 'hora_fin', 'placeholder' => '0:00']) }}
                    {!! $errors->first('hora_fin', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                </div>
            </div>

            <div class="col-12">
                <div class="form-group row">
                    <p>Tiempo de reposición: <span class="text-uppercase" id="tiempo_reposicion">
                            0 horas y 0 minutos
                        </span>
                    </p>

                    <small class="text-danger col-12" id="mensaje"></small>
                </div>
            </div>
        </div>
    </div>
</div>


@section('scripts')
    <script src="{{ asset('js/reposicion_tiempo_scripts.js') }}"></script>
@endsection
