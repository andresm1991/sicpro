<!-- Modal -->
<div class="modal fade" id="tareasModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x"></i>
                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Nueva Tarea</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" enctype="multipart/form-data" id="form_tarea">
                    <div class="row">

                        <div class=" col-12 form-group">
                            <label class="col-form-label">Título</label>
                            {{ Form::select('titulo', pluckTitulosTareas(), '', ['class' => 'select2-tag form-control', 'data-placeholder' => 'SELECCIONE o ingrese título']) }}
                        </div>

                        <div class="col-12 form-group">
                            <label class="col-form-label">Desacripción</label>
                            {{ Form::textarea('descripcion', '', ['class' => 'form-control', 'rows' => '5', 'placeholder' => 'Ingrese el Detalle']) }}
                        </div>

                        <div class="col-12 form-group">
                            <label class="col-form-label">Colaboradores</label>
                            {{ Form::select('list_usuarios[]', usuariosPluck(), 0, ['class' => 'select2-basic-single form-control', 'multiple' => 'multiple', 'data-placeholder' => 'SELECCIONE OPCIONES']) }}
                        </div>

                        @if (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Gerencial'))
                            <div class="col-12 form-group">
                                <label class="col-form-label">Categoria</label>
                                {{ Form::select('categoria_tarea', categoriasAgenda(), '', ['class' => 'select2-tag form-control', 'data-placeholder' => 'SELECCIONE OPCIÓN']) }}
                            </div>
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="guardar">Crear</button>
            </div>
        </div>
    </div>
</div>
