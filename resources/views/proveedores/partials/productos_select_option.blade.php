@if ($slug != 'meteriales.herramientas')
    <div class="form-row">
        <div class="form-group col-md-6">
            <label class="col-form-label ">
                @if ($slug == 'servicios' || $slug == 'contratista')
                    Producto
                @elseif ($slug == 'mano.obra')
                    Categoria
                @elseif ($slug == 'profecionales')
                    Especialidad
                @endif
                <i class="fa-regular fa-asterisk fa-2xs"></i>
            </label>

            <select name="articulos[]" id="productos" class="form-control select2-multiple" multiple="multiple"
                data-placeholder = "Seleccione opción">
                <option></option>
                @foreach ($articulos as $id => $nombre)
                    <option value="{{ $id }}"
                        {{ $proveedor->proveedor_articulos()->where('articulo_id', $id)->exists() ? 'selected' : '' }}>
                        {{ $nombre }}</option>
                @endforeach
            </select>

            {!! $errors->first('articulos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
        </div>

        @if ($slug == 'contratista' || $slug == 'mano.obra')
            <div class="form-group col-md-6">
                <label class="col-form-label ">Calificación <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                {{ Form::number('calificacion', old('calificacion', $proveedor->calificacion), ['class' => 'form-control', 'id' => 'calificacion', 'min' => '1', 'max' => '10', 'step' => '1']) }}
                {!! $errors->first('productos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
            </div>
        @endif

    </div>
@endif
