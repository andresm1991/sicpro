<!-- Modal -->
<div class="modal fade" id="comentarioTareaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x"></i>
                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleComentarioModal"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" enctype="multipart/form-data" id="form_comentario_tarea">
                    {{ Form::hidden('tarea_id') }}
                    <div class="row">
                        <div class="col-12 form-group">
                            <label id="descripcion">Desacripción</label>
                        </div>

                        <div class="col-12 form-group">
                            {{ Form::textarea('comentario', '', ['class' => 'form-control', 'placeholder' => 'Añadir un comentario...', 'rows' => '5']) }}
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="guardar-comentario">Guardar</button>
            </div>
        </div>
    </div>
</div>
