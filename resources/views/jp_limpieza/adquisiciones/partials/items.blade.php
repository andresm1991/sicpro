<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                {{ Form::select('', getProdutosJPLimpieza(), '', ['class' => 'form-control select2-tag', 'data-placeholder' => 'selecciona producto', 'id' => 'producto']) }}
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                {{ Form::text('', old('cantidad'), ['class' => 'form-control input-double', 'id' => 'cantidad']) }}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'V.Unit', ['class' => 'col-form-label']) }}
                {{ Form::text('', old('valor_unitario'), ['class' => 'form-control money', 'id' => 'valor_unitario']) }}
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'iva', ['class' => 'col-form-label']) }}
                {{ Form::text('', old('iva'), ['class' => 'form-control input-enteros', 'id' => 'iva']) }}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'U.medida', ['class' => 'col-form-label']) }}
                {{ Form::select('', getUnidadMedidas(true), '', ['class' => 'form-control select2-tag', 'data-placeholder' => 'selecciona unidad de medida', 'id' => 'unidad_medida']) }}

            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('', 'Necesidad', ['class' => 'col-form-label']) }}
                {{ Form::select('', getJPLimpiezaNecesidades(), '', ['class' => 'form-control select2-tag', 'id' => 'necesidad', 'data-placeholder' => 'selecciona o agrega la necesidad']) }}

            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'total', ['class' => 'col-form-label']) }}
                {{ Form::text('', '', ['class' => 'form-control money', 'id' => 'total', 'placeholder' => '$ 0.0000', 'disabled' => true]) }}
            </div>
        </div>

        <div class="col-sm-2 d-flex align-items-end">
            <div class="form-group">
                <button type="button" class="btn btn-dark" id="agregar-producto">Agregar producto</button>
            </div>
        </div>
    </div>
</fieldset>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad</th>
                <th scope="col">U.Medida</th>
                <th scope="col">V.Unit</th>
                <th scope="col">% iva</th>
                <th scope="col">total</th>
                <th scope="col">Necesidad</th>
                @if (isset($tipoAdquisicion->slug) && strtoupper($tipoAdquisicion->slug) == strtoupper('meteriales.herramientas'))
                    <th scope="col" class="text-center">Inventario</th>
                @endif
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($adquisicion->detalles as $index => $detalle)
                <tr class="elementos-agregados">
                    <td class="align-middle">{{ $index + 1 }}</td>
                    <td class="align-middle">{{ $detalle->producto->nombre }}</td>
                    <td class="align-middle">{{ $detalle->cantidad }}</td>
                    <td class="align-middle">{{ $detalle->unidadMedida->descripcion }}</td>
                    <td class="align-middle">$ {{ $detalle->precio_unitario_formatted }}</td>
                    <td class="align-middle">{{ $detalle->iva }}</td>
                    <td class="align-middle total_unitario">$ {{ $detalle->total_formatted_con_iva }}</td>
                    <td class="align-middle">{{ $detalle->necesidad }}</td>
                    @if (isset($tipoAdquisicion->slug) && strtoupper($tipoAdquisicion->slug) == strtoupper('meteriales.herramientas'))
                        <td class="align-middle">
                            <div class="checkbox-wrapper-8 d-flex justify-content-center align-items-center">
                                {{ Form::hidden('inventario[' . $index . ']', 0) }}
                                <input class="tgl tgl-skewed inventario" name="inventario[{{ $index }}]"
                                    id="cb3-{{ $index }}" type="checkbox" value="0"
                                    {{ isset($detalle->inventario) && $detalle->inventario->pluck('producto_id')->contains($detalle->producto_id) ? 'checked' : '' }}
                                    {{ !auth()->user()->can('jp_limpieza.adquisiciones.editar') && $adquisicion->estado == 'completado'? 'disabled': '' }} />
                                <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI"
                                    for="cb3-{{ $index }}"></label>
                            </div>
                        </td>
                    @endif
                    <td class="align-middle table-actions">
                        <div class="action-buttons">
                            <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto"
                                id="">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </td>
                    {{ Form::hidden('producto[' . $index . ']', $detalle->producto_id) }}
                    {{ Form::hidden('cantidad[' . $index . ']', $detalle->cantidad) }}
                    {{ Form::hidden('unidad_medida[' . $index . ']', $detalle->unidad_medida_id) }}
                    {{ Form::hidden('precio[' . $index . ']', $detalle->precio_unitario) }}
                    {{ Form::hidden('iva[' . $index . ']', $detalle->iva) }}
                    {{ Form::hidden('necesidad[' . $index . ']', $detalle->necesidad) }}
                </tr>
            @endforeach
            <tr id="tr-default" style="display:{{ $adquisicion->id ? 'none' : '' }}">
                <td colspan="{{ isset($tipoAdquisicion->slug) && strtoupper($tipoAdquisicion->slug) == strtoupper('meteriales.herramientas') ? 10 : 9 }}"
                    class="text-center">No existen elementos en la lista...</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-11 d-flex justify-content-end">
        <label class="col-form-label">total general: </label>
    </div>
    <div class="col-1 d-flex justify-content-start">
        <span class="col-form-label" id="total-general"> $
            {{ $adquisicion->total_general_formatted ? $adquisicion->total_general_formatted : '0.0000' }}</span>
    </div>
</div>
