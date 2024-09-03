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
            <select name="articulos[]" id="productos" class="col-12 form-control select2-multiple" multiple="multiple"
                data-placeholder = "Seleccione opción">
                <option></option>
                @foreach ($articulos as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ $proveedor->proveedor_articulos()->where('id', $id)->exists() ? 'selected' : '' }}>
                        {{ $nombre }}</option>
                @endforeach
            </select>

            {!! $errors->first('articulos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
        </div>
        @if ($slug == 'contratista' || $slug == 'mano.obra')
            <div class="col-sm-4">
                <div class="form-group row">
                    <label for="inputCalifi" class="col-sm-6 col-form-label text-right">Calificación <i
                            class="fa-regular fa-asterisk fa-2xs"></i></label>
                    <div class="col-sm-6">
                        {{ Form::number('calificacion', old('calificacion', $proveedor->calificacion), ['class' => 'form-control', 'id' => 'calificacion', 'min' => '1', 'max' => '10', 'step' => '1']) }}
                        {!! $errors->first('productos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
                    </div>
                </div>

            </div>
        @endif

    </div>
@endif
