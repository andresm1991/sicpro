<!-- Modal -->
<div class="modal fade" id="modalPago" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x" style="color: rgba(0, 0, 0, 0.5);"></i>
                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Registrar pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete = "off" enctype = "multipart/form-data" id = "form_pago">
                    <input type="hidden" name="pago_id" value="">
                    <div class="form-group row">
                        <label for="input1" class="col-sm-5 col-form-label">Monto programado</label>
                        <div class="col-sm-7">
                            <input type="text" name="monto_programado" class="form-control money text-right"
                                id="input1" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="input2" class="col-sm-5 col-form-label">Monto pagado</label>
                        <div class="col-sm-7">
                            <input type="text" name="monto_pagado" class="form-control money text-right"
                                id="input2">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="input3" class="col-sm-5 col-form-label">Forma de pago</label>
                        <div class="col-sm-7">
                            {{ Form::select('forma_pago', [], null, ['class' => 'select2-basic-single form-control', 'id' => 'forma_pago', 'data-placeholder' => 'seleccione opción']) }}
                        </div>
                    </div>

                    <div class="form-group" id="message"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="registrar_pago">Registrar Pago</button>
            </div>
        </div>
    </div>
</div>
