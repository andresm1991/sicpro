@if ($slug != 'meteriales.herramientas')
    <div class="form-group row">
        <label for="inputSelect" class="col-sm-2 col-form-label">
            @if ($slug == 'servicios' || $slug == 'contratista')
                Producto
            @elseif ($slug == 'mano.obra')
                Categoria
            @elseif ($slug == 'profecionales')
                Especialidad
            @endif
            <i class="fa-regular fa-asterisk fa-2xs"></i>
        </label>

        <div class="{{ $slug == 'contratista' || $slug == 'mano.obra' ? 'col-sm-6' : 'col-sm-10' }}">
            {{ Form::select('articulos[]', $articulos, old('articulos', $proveedor->proveedor_articulos->pluck('articulo_id')), ['class' => 'selectpicker show-tick show-menu-arrow form-control border ', 'id' => 'mySelect', 'data-style' => '', 'title' => 'Seleccione opción', 'data-header' => count($articulos) > 0 ? 'Seleccione opción' : 'No existen datos registrados.', 'multiple', 'data-selected-text-format' => 'count > 3']) }}

            {!! $errors->first('articulos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
        </div>
        @if ($slug == 'contratista' || $slug == 'mano.obra')
            <div class="col-sm-4">
                <div class="form-group row">
                    <label for="inputCalifi" class="col-sm-6 col-form-label text-right">Calificación <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    <div class="col-sm-6">
                        {{ Form::number('calificacion', old('calificacion', 0), ['class' => 'form-control', 'id' => 'calificacion', 'min' => '1', 'max' => '5', 'step' => '1']) }}
                        {!! $errors->first('productos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                    </div>
                </div>

            </div>
        @endif

    </div>
@endif
