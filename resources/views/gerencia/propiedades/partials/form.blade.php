<div class="row">
    <div class="col-sm-8 col-12">
        <div class="row">
            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">Nombre</label>
                {{ Form::text('nombre', old('nombre', $propiedad->nombre), ['class' => 'form-control', 'placeholder' => 'ingrese nombre de la proiedad']) }}
                {!! $errors->first('nombre', '<small class="help-block text-danger">:message</small>') !!}
            </div>

            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">dirección</label>
                {{ Form::text('direccion', old('direccion', $propiedad->direccion), ['class' => 'form-control', 'placeholder' => 'ingrese la dirección']) }}
                {!! $errors->first('direccion', '<small class="help-block text-danger">:message</small>') !!}
            </div>

            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">área del lote</label>
                {{ Form::text('area', old('area', $propiedad->area), ['class' => 'form-control input-double', 'id' => 'area_lote', 'placeholder' => 'ingrese el área en venta']) }}
                {!! $errors->first('area', '<small class="help-block text-danger">:message</small>') !!}
            </div>

            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">teléfono</label>
                {{ Form::text('telefono', old('telefono', $propiedad->telefono), ['class' => 'form-control', 'placeholder' => 'ingrese teléfono']) }}
                {!! $errors->first('telefono', '<small class="help-block text-danger">:message</small>') !!}
            </div>

            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">tipo</label>
                {{ Form::select('tipo', getTipoPropiedadesVenta(), $propiedad->tipo_propiedad_id, ['class' => 'form-control', 'id' => 'tipo-propiedad', 'data-placeholder' => 'seleccion opción']) }}
                {!! $errors->first('tipo', '<small class="help-block text-danger">:message</small>') !!}
            </div>

            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">precio venta</label>
                {{ Form::text('precio_venta', old('precio_venta', $propiedad->precio_venta_formatted), ['class' => 'form-control moneyDosDecimales', 'id' => 'precio_venta', 'placeholder' => '$ 0.00']) }}
                {!! $errors->first('precio_venta', '<small class="help-block text-danger">:message</small>') !!}
            </div>

            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">precio por MT2</label>
                {{ Form::text('precio_mt2', old('precio_mt2', $propiedad->precio_por_metro_cuadrado_formatted), ['class' => 'form-control moneyDosDecimales', 'id' => 'precio_mt2', 'placeholder' => '$ 0.00', 'readonly' => true]) }}
                {!! $errors->first('precio_mt2', '<small class="help-block text-danger">:message</small>') !!}
            </div>



            <div class="col-sm-4 col-12 form-group" id="metros-construccion"
                style="{{ $propiedad->tipo_propiedad_id && $propiedad->tipo_propiedad->slug == 'tipo.propiedades.casa' ? 'display: block;' : 'display: none;' }}">
                <label class="col-form-label">metros de construcción</label>
                {{ Form::text('metros_construccion', old('metros_construccion', $propiedad->metros_construccion), ['class' => 'form-control input-double', 'id' => 'area-construccion', 'placeholder' => '0']) }}
                {!! $errors->first('metros_construccion', '<small class="help-block text-danger">:message</small>') !!}
            </div>

            <div class="col-sm-4 col-12 form-group">
                <label class="col-form-label">Estado</label>
                {{ Form::select('estado', ['DISPONIBLE' => 'Disponible', 'VENDIDO' => 'Vendido'], $propiedad->estado, ['class' => 'form-control', 'data-placeholder' => 'seleccion opción']) }}
                {!! $errors->first('estado', '<small class="help-block text-danger">:message</small>') !!}
            </div>
            <div class="col-12 form-group">
                <label class="col-form-label">Dimensiones</label>
                <div class="row">
                    <div class="col">
                        {{ Form::text('frente', old('frente', $propiedad->frente), ['class' => 'form-control input-double', 'placeholder' => 'ingrese el frente']) }}
                    </div>
                    <div class="col">
                        {{ Form::text('fondo', old('fondo', $propiedad->fondo), ['class' => 'form-control input-double', 'placeholder' => 'ingrese el fondo']) }}
                    </div>
                </div>
            </div>

            <div class="col-12 form-group">
                <label class="col-form-label">Ubicación</label>
                <div class="row">
                    <div class="col">
                        {{ Form::text('latitud', old('latitud', $propiedad->latitud), ['class' => 'form-control simple-double', 'placeholder' => 'ingrese la latitud']) }}
                    </div>
                    <div class="col">
                        {{ Form::text('longitud', old('longitud', $propiedad->longitud), ['class' => 'form-control simple-double', 'placeholder' => 'ingrese la longitud']) }}
                    </div>
                </div>
                <div class="row form-group col mt-2">
                    <button type="button" class="btn btn-dark btn-sm" id="mi-ubicacion">Obtener coordenadas</button>
                </div>

            </div>

            <div class="col-12">
                <label class="col-form-label">Observaciones</label>
                {{ Form::textarea('observaciones', old('observaciones', $propiedad->observaciones), ['class' => 'form-control', 'rows' => '4', 'placeholder' => 'ingrese observaciones']) }}
            </div>
        </div>

    </div>
    <div class="col-sm-4">
        <div class="row" id="contenedor_imagenes">
            <div class="col-12">
                <label class="col-form-label">Imagenes <button type="button" class="btn btn-secondary btn-sm"
                        id="agregar_imagen"><i class="fa-solid fa-plus"></i></button></label>

                <small id="imagenes_restantes" class="text-muted d-block mb-2">Puedes subir hasta 5 imágenes</small>

                {!! $errors->first('files', '<small class="help-block text-quicksand text-danger">:message</small>') !!}
            </div>

            @forelse ($propiedad->imagenes_propiedades as $index => $file)
                @if ($index == 0)
                    {{-- Input inicial --}}
                    <div class="form-group col-sm-6">
                        {{ Form::file('files[]', [
                            'class' => 'dropify',
                            'data-default-file' => old(
                                'files',
                                isset($file) ? route('imagen.proxy', ['imagen' => $file->id, 'filename' => $file->file_name]) : '',
                            ),
                            'data-id' => $propiedad->id,
                            'data-tipo' => '',
                            'data-height' => '100',
                        ]) }}
                        {{ Form::hidden('imagenes_propiedades[]', $file->id) }}
                    </div>
                @else
                    {{-- Input adicional --}}
                    <div class="form-group col-sm-6 position-relative">
                        {{ Form::file('files[]', [
                            'class' => 'dropify',
                            'data-default-file' => old(
                                'files',
                                isset($file) ? route('imagen.proxy', ['imagen' => $file->id, 'filename' => $file->file_name]) : '',
                            ),
                            'data-id' => $propiedad->id,
                            'data-tipo' => '',
                            'data-height' => '100',
                        ]) }}
                        <button type="button" class="btn btn-sm btn-danger btn-eliminar-imagen" title="Eliminar"
                            style="position: absolute; top: 5px; right: 5px; z-index: 10;">
                            <i class="fa fa-times"></i>
                        </button>
                        {{ Form::hidden('imagenes_propiedades[]', $file->id) }}
                    </div>
                @endif
            @empty
                <div class="form-group col-sm-6">
                    {{ Form::file('files[]', [
                        'class' => 'dropify',
                        'data-default-file' => '',
                        'data-id' => $propiedad->id,
                        'data-tipo' => '',
                        'data-height' => '100',
                    ]) }}
                </div>
            @endforelse
            {{-- Input inicial --}}

        </div>
    </div>

    @section('scripts')
        <script src="{{ asset('js/propiedades.js?v=' . config('app.version', '')) }}"></script>
    @endsection
