<!-- Modal -->
<div class="modal fade" id="agregarProductosModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x"></i>
                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Agregar productos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" enctype="multipart/form-data" id="form_agregar_productos">
                    {{ Form::hidden('adquisicion_id', $adquisicion->id) }}
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="form-group">
                                {{ Form::label('', 'Productos', ['class' => 'col-form-label']) }}
                                {{ Form::select('producto', produtosPluck(), '', ['class' => 'select2-tag', 'id' => 'producto', 'data-placeholder' => 'selecciona o agrega el producto']) }}

                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                                {{ Form::text('cantidad', old('cantidad'), ['class' => 'form-control input-double', 'id' => 'cantidad']) }}
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                {{ Form::label('', 'Valor Unitario', ['class' => 'col-form-label']) }}
                                {{ Form::text('valor_unitario', 0, ['class' => 'form-control money', 'id' => 'valor_unitario']) }}
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                {{ Form::label('', 'IVA %', ['class' => 'col-form-label']) }}
                                {{ Form::text('iva', 0, ['class' => 'form-control input-enteros', 'id' => 'iva']) }}
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('', 'Unidad Media', ['class' => 'col-form-label']) }}
                                {{ Form::select('unidad_medida', getUnidadMedidas(true), '', ['class' => 'select2-tag', 'id' => 'unidad_medida', 'data-placeholder' => 'selecciona o agrega el producto']) }}

                            </div>
                        </div>

                        <div class="col-sm-8">
                            <div class="form-group">
                                {{ Form::label('', 'Necesidad', ['class' => 'col-form-label']) }}
                                {{ Form::select('necesidad', getNecesidades(true), '', ['class' => 'select2-tag', 'id' => 'necesidad', 'data-placeholder' => 'selecciona o agrega la necesidad']) }}

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="agregar-producto">Agregar</button>
            </div>
        </div>
    </div>
</div>
