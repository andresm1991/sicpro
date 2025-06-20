<!-- Modal -->
<div class="modal fade" id="modalMovimientoCaja" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x" style="color: rgba(0, 0, 0, 0.5);"></i>
                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Movimiento Caja</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete = "off" enctype = "multipart/form-data" id = "form_movimiento_caja">
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Monto</label>
                        <div class="col-sm-7">
                            <input type="text" name="monto" class="form-control money text-right" id="monto">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Tipo de Movimiento</label>
                        <div class="col-sm-7">
                            {{ Form::select('tipo_movimiento', ['ingreso' => 'ingreso', 'egreso' => 'egreso'], null, ['class' => 'select2-basic-single form-control', 'id' => 'tipo_movimiento', 'data-placeholder' => 'seleccione opción']) }}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Proveedor (opcional)</label>
                        <div class="col-sm-7">
                            {{ Form::select('proveedor', getProveedores(true), null, ['class' => 'select2-basic-single form-control', 'id' => 'proveedor', 'data-placeholder' => 'seleccione proveedor']) }}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">referencia (opcional)</label>
                        <div class="col-sm-7">
                            <input type="text" name="referencia" id="referencia" class="form-control"
                                placeholder="ingrese referecia">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Detalle</label>
                        <div class="col-sm-7">
                            <textarea name="detalle" id="detalle" class="form-control" rows="2"
                                placeholder="Ingrese un detalle del movimiento"></textarea>
                        </div>
                    </div>

                    <div class="form-group" id="message"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="registrar_movimiento">Registrar movimiento</button>
            </div>
        </div>
    </div>
</div>
