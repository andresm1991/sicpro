<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            @if ($orden_trabajo->proveedor_id)
                {{ Form::label('', $orden_trabajo->proveedor->razon_social, ['class' => 'form-control label-disabled text-uppercase']) }}
            @else
                <select name="proveedor" id="proveedor" class="form-control select2-basic-single"
                    data-placeholder="Selecione proveedor">
                    <option></option>
                    @foreach ($proveedores as $id => $nombre)
                        <option value="{{ $id }}" {{ $orden_trabajo->proveedor_id == $id ? 'selected' : '' }}>
                            {{ $nombre }}</option>
                    @endforeach
                </select>
            @endif

        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
            @if ($orden_trabajo->proveedor_id)
                {{ Form::label('', $orden_trabajo->articulo->descripcion, ['class' => 'form-control label-disabled text-uppercase']) }}
            @else
                <select name="categoria" id="categoria" class="form-control select2-basic-single"
                    data-placeholder="Selecione categoría">
                    <option></option>

                </select>
            @endif
        </div>
    </div>

    <div class="col-sm-2 col-12">
        <div class="form-group">
            {{ Form::label('', 'Plazo semanas', ['class' => 'col-form-label']) }}
            {{ Form::text('plazo_semanas', old('plazo_semanas'), ['class' => 'form-control input-enteros', 'id' => 'plazo_semanas']) }}
        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'subproyecto', ['class' => 'col-form-label']) }}
            {{ Form::select('subproyecto', $subproyectos, old('subproyecto', $orden_trabajo->subproyecto), ['class' => 'form-control select2-tag', 'data-placeholder' => 'subproyecto', 'data-allow-clear' => 'true']) }}
        </div>
    </div>
</div>

@if (!isset($administrativo))
    <legend class="custom-legend"><span>Agregar Productos</span></legend>
    <fieldset class="custom-fieldset">
        <div class="row">
            <div class="col-md-4 col 12">
                <div class="form-group">
                    {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                    <select name="producto" id="producto" class="form-control select2-tag"
                        data-placeholder="Selecione producto">
                        <option></option>
                        @foreach ($articulos as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-12">
                <div class="form-group">
                    {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                    <input type="text" name="cantidad" class="form-control input-double" placeholder="0" />
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-12">
                <div class="form-group">
                    {{ Form::label('', 'Unidad medida', ['class' => 'col-form-label']) }}
                    <select name="unidad-medida" id="unidad-medida" class="form-control select2-tag"
                        data-placeholder="Selecione opción">
                        <option></option>
                        @foreach ($unidades_medidas as $id => $medida)
                            <option value="{{ $medida->id }}">{{ $medida->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-12">
                <div class="form-group">
                    {{ Form::label('', 'Precio unitario', ['class' => 'col-form-label']) }}
                    <input type="text" name="precio-unitario" class="form-control input-double"
                        placeholder="$0,00" />
                </div>
            </div>
            <div class="col-sm-12 col-md-12 col-12">
                <div class="form-group">
                    <button type="button" class="btn btn-dark" id="agregar-producto">Agregar producto</button>
                </div>

            </div>
        </div>
    </fieldset>
@endif
@include('contratista.partials.items')


@section('scripts')
    <script>
        var url =
            "{{ !isset($administrativo) ? route('proyecto.adquisiciones.contratista', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id]) : '' }}";
    </script>
    <script src="{{ asset('js/orden_trabajo_contratistas_scripts.js?v=' . config('app.version', '')) }}" type="module">
    </script>
@endsection
