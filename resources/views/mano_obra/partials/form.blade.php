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
    
    <div class="form-group">
        <button type="button" class="btn btn-dark btn-clonar">Agregar Fila</button>
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
                @foreach  ($mano_obra as $element)
                    
                @endforeach
                @else
                <tr class="fila-clonable">
                   <td>
                        <select name="personal[]" class="form-control select2-basic-single" data-placeholder="Selecione personal">
                            <option></option>
                            @foreach ($proveedores as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                   </td>
                   <td>
                        <select name="categoria[]" class="form-control select2-basic-single" data-placeholder="Selecione categoría">
                            <option></option>
                            @foreach ($proveedores as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                   </td>
                   <td>
                    <select name="jornada[]" class="form-control select2-basic-single" data-placeholder="Selecione jornada">
                        <option></option>
                        <option value="MEDIO TIEMPO">MEDIO TIEMPO</option>
                        <option value="COMPLETA">COMPLETA</option>
                    </select>
                   </td>
                   <td>
                        {{ Form::text('valor', old('valor'), ['class' => 'form-control input-double', 'placeholder' => '$ 0.00', 'data-inputmask' => "'alias': 'currency'"]) }}
                   </td>
                   <td>
                        {{ Form::text('adicional', old('adicional'), ['class' => 'form-control input-double', 'placeholder' => '$ 0.00', 'data-inputmask' => "'alias': 'currency'"]) }}
                   </td>
                   <td>
                        {{ Form::text('detalle_adicional', old('detalle_adicional'), ['class' => 'form-control', ]) }}
                   </td>
                   <td>
                        {{ Form::text('descuento', old('descuento'), ['class' => 'form-control input-double', 'placeholder' => '$ 0.00', 'data-inputmask' => "'alias': 'currency'"]) }}
                   </td>
                   <td>
                        {{ Form::text('detalle_descuento', old('detalle_descuento'), ['class' => 'form-control', ]) }}
                   </td>
                   <td>{{ Form::text('observaciones', old('observaciones'), ['class' => 'form-control', ]) }}</td>
                   <td>
                    <button type="button" class="btn btn-dark ">-</button>
                   </td>
                </tr>
                @endif
                
            </tbody>
        </table>
    </div>

</div>

@section('scripts')
<script src="{{ asset('js/mano_obra_scripts.js') }}"></script>
@endsection