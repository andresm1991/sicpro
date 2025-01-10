<legend class="custom-legend"><span>Agregar Productos</span></legend>
<fieldset class="custom-fieldset">
    <div class="row">
        <div class="col-sm-5">
            <div class="form-group">
                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                <select name="productos" id="productos" class="form-control"
                    data-placeholder="selecciona o agrega el producto">
                    <option></option>
                    @foreach ($productos as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                {{ Form::text('cantidad', old('cantidad'), ['class' => 'form-control input-double', 'id' => 'cantidad']) }}
            </div>
        </div>
        <div class="col-sm-5">
            <div class="form-group">
                {{ Form::label('', 'Necesidad', ['class' => 'col-form-label']) }}
                <select name="necesidad" id="necesidad" class="form-control"
                    data-placeholder="selecciona o agrega la necesidad">
                    <option></option>
                    @foreach (palabras() as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{ Form::hidden('slug_adquisicion', isset($tipo_etapa->slug) ? strtoupper($tipo_etapa->slug) : '') }}

    @if (isset($tipo_etapa->slug) && strtoupper($tipo_etapa->slug) == 'SERVICIOS')
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group">
                    {{ Form::label('', 'Unidad de Medida', ['class' => 'col-form-label']) }}
                    <select name="unidad" id="unidad_medida" class="form-control" data-placeholder="selecciona opción">
                        <option></option>
                        @foreach ($unidad_medidas as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-2">
                <div class="form-group">
                    {{ Form::label('', 'Precio', ['class' => 'col-form-label']) }}
                    {{ Form::text('precio_unitario', old('precio_unitario', $orden_pedido->precio), ['class' => 'form-control currency']) }}
                </div>
            </div>
        </div>
    @endif


    <div class="form-group">
        <button type="button" class="btn btn-dark" id="add-producto-adquisicion">Agregar</button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>No es posible completar el pedido, por favor verifique que existan elementos agregados a la lista
                del pedido.</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

</fieldset>

<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th scope="col">Item</th>
                <th scope="col">Producto</th>
                <th scope="col">Cantidad</th>
                @if (isset($tipo_etapa->slug) && strtoupper($tipo_etapa->slug) == 'SERVICIOS')
                    <th scope="col">Unidad</th>
                    <th scope="col">Valor</th>
                @endif
                <th scope="col" class="col-gasolina"
                    style="display:{{ $orden_pedido->adquisiciones_detalle->firstWhere('kilometraje', '!=', null) ? '' : 'none' }}">
                    KM</th>
                <th scope="col">Necesidad</th>
                @if (isset($tipo_etapa->slug) && strtoupper($tipo_etapa->slug) == strtoupper('meteriales.herramientas'))
                    <th scope="col" class="text-center">Inventario</th>
                @endif
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orden_pedido->adquisiciones_detalle as $index => $element)
                <tr class="elementos-agregados">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $element->producto->descripcion }}</td>
                    <td class="edit-item">
                        <span>{{ $element->cantidad_solicitada }}</span>
                        <div class="d-flex align-items-center hidden">
                            <input type="numbre" class="form-control mr-2 input-double" name="cantidad[]"
                                value="{{ $element->cantidad_solicitada }}" step="0.01">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>

                    @if (isset($tipo_etapa->slug) && strtoupper($tipo_etapa->slug) == 'SERVICIOS')
                        <td>{{ $element->unidad_medida->descripcion }}</td>
                        <td>{{ number_format($element->valor, 2) }} </td>
                        {{ Form::hidden('unidad_medida[]', $element->unidad_medida_id) }}
                        {{ Form::hidden('precio[]', $element->valor) }}
                    @endif

                    <td class="edit-item col-gasolina"
                        style="display:{{ $orden_pedido->adquisiciones_detalle->firstWhere('kilometraje', null) ? 'none' : '' }}">
                        <span>{{ $element->kilometraje }}</span>
                        <div class="d-flex align-items-center hidden">
                            <input type="text" class="form-control mr-2" name="km[]"
                                value="{{ $element->kilometraje }}">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                    <td class="edit-item">
                        <span>{{ $element->necesidad }}</span>
                        <div class="d-flex align-items-center hidden">
                            <input type="text" class="form-control mr-2" name="necesidad[]"
                                value="{{ $element->necesidad }}">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                    @if (isset($tipo_etapa->slug) && strtoupper($tipo_etapa->slug) == strtoupper('meteriales.herramientas'))
                        <td class="align-middle">
                            <div class="checkbox-wrapper-8 d-flex justify-content-center align-items-center">
                                {{ Form::hidden('inventario[' . $index . ']', 0) }}
                                <input class="tgl tgl-skewed inventario" name="inventario[{{ $index }}]"
                                    id="cb3-{{ $index }}" type="checkbox" value="0"
                                    {{ isset($orden_pedido->orden_recepcion->inventario) && $orden_pedido->orden_recepcion->inventario->pluck('producto_id')->contains($element->articulo_id) ? 'checked' : '' }}
                                    {{ isset($orden_pedido->orden_recepcion) && !$orden_pedido->orden_recepcion->editar ? 'disabled' : '' }} />
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
                    <input type="hidden" name="productos[]" value="{{ $element->articulo_id }}">
                </tr>
            @endforeach
            <tr id="tr-default" style="display:{{ $orden_pedido->id ? 'none' : '' }}">
                <td colspan="6" class="text-center">No existen elementos en la lista...</td>
            </tr>

        </tbody>
    </table>

    @if (isset($orden_pedido->orden_recepcion->completado) && $orden_pedido->orden_recepcion->completado)
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fa-regular fa-triangle-exclamation fa-3x"></i>
            <small class="mx-4">La orden de recepción fue completada. Por motivos de seguridad, si desea
                actualizar la información, por favor solicite al administrador que habilite esta orden. Para
                hacerlo, haga clic en el siguiente enlace: <a href="#" class="text-dark font-weight-bold">
                    Solicitar
                    edición de la orden.</a></small>
        </div>
    @endif
</div>
