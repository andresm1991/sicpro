<div class="row">
    <div class="col-sm-8">
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Nombre del Proyecto <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('nombre_proyecto', old('nombre_proyecto', $proyecto->nombre_proyecto), ['class' => 'form-control text-capitalize', 'placeholder' => 'Ingrese nombre del proyecto']) }}
                    {!! $errors->first('nombre_proyecto', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Teléfono de contacto </label>
                    {{ Form::text('telefono', old('telefono', $proyecto->telefono), ['class' => 'form-control input-enteros', 'placeholder' => 'Ingrese teléfono']) }}
                    {!! $errors->first('telefono', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Entidad <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('entidad', old('entidad', $proyecto->entidad), ['class' => 'form-control text-capitalize', 'placeholder' => 'Ingrese nombre del propietario']) }}
                    {!! $errors->first('entidad', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                </div>
            </div>

            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Metraje contratado <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('metraje_contratado', old('metraje_contratado', $proyecto->metros_contratado), ['class' => 'form-control input-double', 'placeholder' => 'Ingrese el metraje total']) }}
                    {!! $errors->first(
                        'metraje_contratado',
                        '<small class="help-block text-quicksand text-danger">:message</small>',
                    ) !!}
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Precio por metros <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('precio_metro', old('precio_metro', $proyecto->precio_por_metro), ['class' => 'form-control money', 'placeholder' => '$ 0.0000']) }}
                    {!! $errors->first('precio_metro', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                </div>
            </div>

            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Tiempo contratado Meses <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('tiempo_contratado', old('tiempo_contratado', $proyecto->tiempo_contratado), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese el valor']) }}
                    {!! $errors->first('tiempo_contratado', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('', 'valor mensual', ['class' => 'col-form-label']) }}
                    {{ Form::label('', $proyecto->valor_contratado_mensual_formatted, ['class' => 'form-control label-disabled', 'id' => 'valor_mensual']) }}
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    {{ Form::label('', 'total contratado', ['class' => 'col-form-label']) }}
                    {{ Form::label('', $proyecto->total_contratado_formatted, ['class' => 'form-control label-disabled', 'id' => 'total_contratado']) }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Fecha de inicio <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('fecha_inicio', old('fecha_inicio', \Carbon\Carbon::parse($proyecto->fecha_inicio)->format('d-m-Y')), ['class' => 'form-control datepicker', 'placeholder' => 'Ingrese la fecha de inicio']) }}
                    {!! $errors->first('fecha_inicio', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label class="col-form-label font-weight-bold">Fecha de finalización <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    {{ Form::text('fecha_fin', old('fecha_fin', \Carbon\Carbon::parse($proyecto->fecha_finalizacion)->format('d-m-Y')), ['class' => 'form-control datepicker', 'placeholder' => 'Ingrese la decha de finalización', 'readonly' => true]) }}
                    {!! $errors->first('fecha_fin', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-form-label font-weight-bold">Observaciones</label>
            {{ Form::textarea('observaciones', old('observaciones', $proyecto->observacion), ['class' => 'form-control', 'placeholder' => 'Ingrese observaciones', 'rows' => '5']) }}
            {!! $errors->first('observaciones', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            <div class="col-12 p-0">
                {!! $errors->first('portada', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
            </div>
            <div class="file-input-container">
                <div class="row">
                    <div class="col-12">
                        <h5 class="col-form-label">Portada del Proyecto</h5>
                    </div>
                    <div class="col-lg-12 file-input-wrapper">
                        <img src="{{ $proyecto->archivo_portada ? doTemporaryUrl($proyecto->archivo_portada) : asset('images/no-fotos.png') }}"
                            class="card-img-top" id="preview-portada" alt="portada">
                        <label for="singleFileInput" class="file-input-label">Elige otro
                            archivo...</label>

                    </div>
                </div>

                <input type="file" id="singleFileInput" name="portada">
            </div>
            <small>Archivos permitidos: JPG, JPEG, PNG</small>
        </div>
        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-form-label">Orden de compra
                    @isset($proyecto->archivo_orden_compra)
                        <a href="{{ doTemporaryUrl($proyecto->archivo_orden_compra) }}" target="_blank"
                            class="btn btn-sm btn-dark"><i class="fa-solid fa-eye"></i></a>
                    @endisset
                </label>
                <div id="load_img">
                    {{ Form::file('file_orden_compra', ['class' => 'dropify', 'data-default-file' => old('file_orden_compra', isset($proyecto->archivo_orden_compra) ? $proyecto->archivo_orden_compra : ''), 'data-id' => $proyecto->id, 'data-tipo' => 'archivo_orden_compra', 'data-height' => '100']) }}
                </div>

                {!! $errors->first('file_orden_compra', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
            </div>

            <div class="form-group col-sm-6">
                <label class="col-form-label">Acta final
                    @isset($proyecto->archivo_acta_final)
                        <a href="{{ doTemporaryUrl($proyecto->archivo_acta_final) }}" target="_blank"
                            class="btn btn-sm btn-dark"><i class="fa-solid fa-eye"></i></a>
                    @endisset
                </label>
                <div id="load_img">
                    {{ Form::file('file_acta_final', ['class' => 'dropify', 'data-default-file' => old('file_acta_final', isset($proyecto->archivo_acta_final) ? $proyecto->archivo_acta_final : ''), 'data-id' => $proyecto->id, 'data-tipo' => 'archivo_acta_final', 'data-height' => '100']) }}
                </div>

                {!! $errors->first('file_acta_final', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
            </div>
        </div>

    </div>
</div>

@section('scripts')
    <script src="{{ asset('js/jp_limpieza.js') }}"></script>
@endsection


{{-- 
{{ Form::file('logo', ['class' => 'dropify', 'data-default-file' => old('logo', !is_null($datos->childrenCatalogoDatos[6]->value)? Storage::url($datos->childrenCatalogoDatos[6]->value) : ''), 'data-file' => $datos->childrenCatalogoDatos[6]->value, 'data-tipo' => 'portada', 'data-height' => '100' ]) }}
 --}}
