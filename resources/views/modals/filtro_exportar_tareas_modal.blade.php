<!-- Modal -->
<div class="modal fade" id="filtroExportarTareasModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Exportar tareas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" enctype="multipart/form-data" id="form_filtro_tareas">
                    <div class="row">
                        <div class=" col-12 form-group">
                            <label class="col-form-label">Estado</label>
                            {{ Form::select('estado', getEstadosTarea(), '', ['class' => 'form-control select2-basic-single', 'data-placeholder' => 'SELECCIONE opción']) }}
                        </div>

                        <div class="col-12 form-group">
                            <label class="col-form-label">Colaboradores</label>
                            {{ Form::select('usuario', usuariosPluck(), '', ['class' => 'select2-basic-single form-control', 'data-placeholder' => 'SELECCIONE OPCIóN']) }}
                        </div>

                        @if (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Gerencial'))
                            <div class="col-12 form-group">
                                <label class="col-form-label">Categoria</label>
                                {{ Form::select('categoria_tarea', categoriasAgenda(), '', ['class' => 'select2-basic-single form-control', 'data-placeholder' => 'SELECCIONE OPCIÓN']) }}
                            </div>
                        @endif
                    </div>
                </form>

                <small class="col-form-label font-size-11">Importante. </small><br>
                <small class="font-size-11">Si no seleccionas filtros, se exportarán todas las tareas. Si aplicas
                    filtros solo se exportarán
                    las tareas correspondientes.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="exportar-tarea">Exportar</button>
            </div>
        </div>
    </div>
</div>
