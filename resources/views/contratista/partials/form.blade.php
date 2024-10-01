<div class="row">
    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Proveedor', ['class' => 'col-form-label']) }}
            <select name="proveedor" id="proveedor" class="form-control select2-basic-single"
                data-placeholder="Selecione proveedor">
                <option></option>
                @foreach ($proveedores as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
            <select name="categoria" id="categoria" class="form-control select2-basic-single"
                data-placeholder="Selecione categoría">
                <option></option>

            </select>
        </div>
    </div>

    <div class="col-sm-4 col-12">
        <div class="form-group">
            {{ Form::label('', 'Unidad de Medida', ['class' => 'col-form-label']) }}
            <select name="medida" id="medida" class="form-control select2-basic-single"
                data-placeholder="Selecione opción">
                <option></option>
                @foreach ($unidades_medidas as $id => $medida)
                    <option value="{{ $id }}">{{ $medida->descripcion }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        var url =
            "{{ route('proyecto.adquisiciones.contratista', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id]) }}";
    </script>
    <script src="{{ asset('js/orden_trabajo_contratistas_scripts.js') }}" type="module"></script>
@endsection
