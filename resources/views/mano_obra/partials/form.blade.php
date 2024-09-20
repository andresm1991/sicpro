<div class="row">
    <div class="col-md-7 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Proyecto', ['class' => 'col-sm-2 col-form-label']) }}
            <div class="col-sm-10">
                {{ Form::text('proyecto', $proyecto->nombre_proyecto, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
            </div>
        </div>
    </div>
    <div class="col-md-5 col-12">
        <div class="form-group row">
            {{ Form::label('', 'Etapa', ['class' => 'col-sm-2 col-form-label']) }}
            <div class="col-sm-10">
                {{ Form::text('etapa', $tipo_adquisicion->descripcion, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            <legend class="custom-legend"><span>Agregar Personal</span></legend>
            <fieldset class="custom-fieldset">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('', 'Personal', ['class' => 'col-form-label']) }}
                            <select name="personal" id="personal" class="form-control select2-basic-single"
                                data-placeholder="Selecione personal">
                                <option></option>
                                @foreach ($proveedores as $id => $nombre)
                                    <option value="{{ $id }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
                            <select name="categoria" id="categoria" class="form-control select2-basic-single"
                                data-placeholder="Selecione categoría">
                                <option></option>

                            </select>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                            {{ Form::label('', 'Jornada', ['class' => 'col-form-label']) }}
                            <select name="jornada" id="jornada" class="form-control select2-basic-single"
                                data-placeholder="Selecione jornada">
                                <option></option>
                                <option value="MEDIO TIEMPO">MEDIO TIEMPO</option>
                                <option value="COMPLETA">COMPLETA</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-1">
                        <div class="form-group">
                            {{ Form::label('', 'Valor', ['class' => 'col-form-label']) }}
                            {{ Form::text('valor', old('valor'), ['class' => 'form-control ', 'placeholder' => '$ 0.00', 'data-inputmask' => "'alias': 'currency'"]) }}
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="form-group">
                            {{ Form::label('', 'Adicional', ['class' => 'col-form-label']) }}
                            {{ Form::text('adicional', old('adiconal'), ['class' => 'form-control ', 'placeholder' => '$ 0.00', 'data-inputmask' => "'alias': 'currency'"]) }}
                        </div>
                    </div>
                    <div class="col-sm-1">
                        <div class="form-group">
                            {{ Form::label('', 'Descuento', ['class' => 'col-form-label']) }}
                            {{ Form::text('descuento', old('descuento'), ['class' => 'form-control ', 'placeholder' => '$ 0.00', 'data-inputmask' => "'alias': 'currency'"]) }}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('', 'Detalle adicional', ['class' => 'col-form-label']) }}
                            {{ Form::text('detalle_adicional', old('detalle_adicional'), ['class' => 'form-control', 'placeholder' => '']) }}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('', 'Detalle descuento', ['class' => 'col-form-label']) }}
                            {{ Form::text('detalle_descuento', old('detalle_descuento'), ['class' => 'form-control', 'placeholder' => '']) }}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {{ Form::label('', 'Observaciones', ['class' => 'col-form-label']) }}
                            {{ Form::text('observaciones', old('observaciones'), ['class' => 'form-control', 'placeholder' => '']) }}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-dark" id="add-personal">Agregar Personal</button>
                </div>
            </fieldset>
        </div>
    </div>

</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover" id="tabla-planificacion">
        <thead>
            <tr>
                <th scope="col">Personal</th>
                <th scope="col">Categoría</th>
                <th scope="col">Jornada</th>
                <th scope="col">Valor</th>
                <th scope="col">Adicional</th>
                <th scope="col">Detalle Adicional</th>
                <th scope="col">Descuento</th>
                <th scope="col">Detalle Descuento</th>
                <th scope="col">Observaciones</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($mano_obra->getAttributes()))
                @foreach ($mano_obra as $element)
                @endforeach
            @else
            @endif
            <tr id="tr-default" style="display:{{ $mano_obra->id ? 'none' : '' }}">
                <td colspan="10" class="text-center">No existen elementos en la lista...</td>
            </tr>
        </tbody>
    </table>
</div>

@section('scripts')
    <script src="{{ asset('js/mano_obra_scripts.js') }}"></script>
@endsection
