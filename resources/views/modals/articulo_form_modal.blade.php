<!-- Modal -->
<div class="modal fade" id="articuloFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->

            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary" id="titleArticuloFormModal">Nuevo Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="from-articulo">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Categoría <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                        {{ Form::select('categoria', $categorias->pluck('descripcion', 'id'), old('categoria'), ['class' => 'selectpicker show-tick show-menu-arrow form-control border', 'data-live-search' => count($categorias) > 0 ? 'true' : 'false', 'data-style' => '', 'title' => 'Seleccione categoía', 'data-header' => count($categorias) > 0 ? 'Seleccione opción' : 'No existen datos registrados.']) }}

                        <div class="error_categoria"></div>
                    </div>
                    <div class="form-group">
                        <label>Código <i class="fa-regular fa-circle-exclamation text-success" data-toggle="tooltip"
                                data-placement="top"
                                title="Código que servirá como referencia para este producto, ejemplo: COD1234."></i></label>
                        <input type="text" name="codigo" class="form-control text-uppercase"
                            placeholder="Ingrese un código">
                    </div>

                    <div class="form-group">
                        <label>Descripción <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                        <input type="text" name="descripcion" class="form-control"
                            placeholder="Ingrese la descripción del producto">
                    </div>

                    <div class="form-group">
                        <label for="inputnamegenero">Estado</label>
                        <div class="row">
                            <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <div class="radio radio-inline">
                                        {{ Form::radio('status', '1', true, ['id' => 'check_enabled']) }}
                                        <label for="check_enabled" class="text-success">
                                            <i class="helper"></i> Activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group pureCSS">
                                    <div class="radio radio-inline">
                                        {{ Form::radio('status', '0', null, ['id' => 'chek_disabled']) }}
                                        <label for="chek_disabled" class="text-danger">
                                            <i class="helper"></i>Inactivo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="message"></div>
                    <div class="col-12 p-0">
                        <p class="font-italic">Los campos con (*) son obligatorios, por favor complétalos corretéame</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-dark" id="guardar-articulo">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
