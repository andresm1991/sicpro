<!-- Modal -->
<div class="modal fade" id="modalCreateForm" tabindex="-1" role="dialog" aria-labelledby="modalCreateFormTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            {{-- @include('partials.loader_overlay') --}}
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary" id="titleModalCreateForm">Nuevo Formulario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_create">
                <div class="modal-body">
                    <form id="dynamicForm" class="form-horizontal">
                        <div class="form-group">
                            <div id="formFields">
                                <div class="field-container">
                                    <div class="row">
                                        <div class="col-auto mt-2">
                                            <label for="field1" class="editable-label">Campo 1:</label>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" name="field[]" id="field1"
                                                placeholder="Campo 1">
                                        </div>
                                        <button type="button" class="btn btn-danger delete-button mx-2"><i
                                                class="fa-regular fa-xmark"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <select id="fieldTypeSelector">
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                            <option value="email">Correo Electrónico</option>
                            <option value="date">Fecha</option>
                        </select>
                        <button type="button" id="addFieldButton">Añadir Campo</button>
                        <button type="submit">Enviar</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="save-cargo">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
