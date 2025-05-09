<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-md-4 col 12">
            <div class="form-group">
                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                {{ Form::select('', getProdutosJPLimpieza(), '', ['class' => 'form-control select2-tag', 'id' => 'producto', 'data-placeholder' => 'Selecione producto']) }}
            </div>
        </div>

        <div class="col-sm-2 col-md-2 col-12">
            <div class="form-group">
                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                {{ Form::text('', '', ['class' => 'form-control input-double', 'id' => 'cantidad', 'placeholder' => '0']) }}
            </div>
        </div>

        <div class="col-sm-2 col-md-2 col-12">
            <div class="form-group">
                {{ Form::label('', 'Unidad medida', ['class' => 'col-form-label']) }}
                {{ Form::select('', getUnidadMedidas(true), '', ['class' => 'form-control select2-tag', 'id' => 'unidad-medida', 'data-placeholder' => 'Selecione opción']) }}
            </div>
        </div>

        <div class="col-sm-2 col-md-2 col-12">
            <div class="form-group">
                {{ Form::label('', 'Precio unitario', ['class' => 'col-form-label']) }}
                {{ Form::text('', '', ['class' => 'form-control money', 'id' => 'precio-unitario', 'placeholder' => '$0,0000']) }}
            </div>
        </div>
        <div class="col-sm-2 col-md-2 col-12">
            <div class="form-group">
                {{ Form::label('', 'iva', ['class' => 'col-form-label']) }}
                {{ Form::text('', 0, ['class' => 'form-control input-enteros', 'id' => 'iva', 'placeholder' => '0']) }}
            </div>
        </div>
        <div class="col-sm-12 col-md-12 col-12">
            <div class="form-group">
                <button type="button" class="btn btn-dark" id="agregar-producto">Agregar producto</button>
            </div>

        </div>
    </div>
</fieldset>

<div class="table-responsive">
    <table class="table table-bordered table-hover" id="tabla-productos">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad</th>
                <th scope="col">U.Medida</th>
                <th scope="col">precio unit.</th>
                <th scope="col">iva</th>
                <th scope="col">Total</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contratista->detalles as $index => $detalle)
                <tr class="elementos-agregados">
                    <td class="aling-middle">{{ $index + 1 }}</td>
                    <td class="aling-middle">
                        {{ $detalle->producto->nombre }}
                    </td>
                    <td class="aling-middle">
                        {{ $detalle->cantidad }}
                    </td>
                    <td class="aling-middle">
                        {{ $detalle->unidadMedida->descripcion }}
                    </td>
                    <td class="aling-middle">
                        $ {{ $detalle->precio_unitario_formatted }}
                    </td>
                    <td class="aling-middle">
                        {{ $detalle->iva }}
                    </td>
                    <td class="aling-middle total_unitario">
                        $ {{ $detalle->total_formatted }}
                    </td>
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
                </tr>
            @endforeach
            <tr id="tr-default" style="display:{{ $contratista->detalles->isEmpty() ? '' : 'none' }}">
                <td colspan="8" class="text-center">
                    <span>No existen elementos agregados</span>
                </td>
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
            {{ $contratista->total_contratado_formatted ? $contratista->total_contratado_formatted : '0.0000' }}</span>
    </div>
</div>

@section('scripts')
    <script src="{{ asset('js/jp_limpieza/contratistas.js') }}"></script>
@endsection
