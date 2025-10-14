<!-- Modal para Editar Item -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-outline card-primary">
            <div class="modal-header">
                <h5 class="modal-title">Editar Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editItemForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 form-group">
                            <label for="modalItemNombre" class="col-form-label">Nombre</label>
                            <input type="text" class="form-control" id="modalItemNombre" name="nombre" required>
                        </div>
                        <div class="col-12 form-group">
                            <label for="modalItemEstado" class="col-form-label">Estado</label>
                            <select class="select2-basic-single form-control" data-minimum-results-for-search="Infinity"
                                id="modalItemEstado" name="estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="entregado">Entregado</option>
                                <option value="en_revision">En Revisión</option>
                                <option value="aprobado">Aprobado</option>
                            </select>
                        </div>
                        <div class="col-12 form-group">
                            <label for="modalItemObservaciones" class="col-form-label">Observaciones</label>
                            <textarea class="form-control" id="modalItemObservaciones" name="observaciones" rows="3"></textarea>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-dark" id="guardar-edit-item">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
