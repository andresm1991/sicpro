<!-- Modal -->
<div class="modal fade" id="modalPlanificacionManoObra" tabindex="-1" role="dialog" aria-hidden="true">
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
                <h5 class="modal-title font-weight-bold text-dark" id="titleModalPlanificacionManoObra">Nueva
                    Planificación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete = "off" enctype = "multipart/form-data" id = "form_planificacion_mano_obra">
                    {{ Form::hidden('tipo', $tipo) }}
                    {{ Form::hidden('tipo_id', $tipo_id) }}
                    {{ Form::hidden('tipo_adquisicion', $tipo_adquisicion->id) }}
                    {{ Form::hidden('proyecto_id', $proyecto->id) }}
                    {{ Form::hidden('tipo_etapa', $tipo_etapa->id) }}

                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Proyecto', ['class' => 'col-form-label']) }}
                                {{ Form::text('proyecto', $proyecto->nombre_proyecto, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Etapa', ['class' => 'col-form-label']) }}
                                {{ Form::text('etapa', $tipo_adquisicion->descripcion, ['class' => 'form-control text-capitalize', 'readonly', '']) }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Fecha Inicio', ['class' => 'col-form-label']) }}
                                {{ Form::text('fecha_inicio', old('fecha_inicio'), ['class' => 'form-control datepicker-no-back', 'placeholder' => 'Ingrese fecha de inicio']) }}
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Fecha Fin', ['class' => 'col-form-label']) }}
                                {{ Form::text('fecha_fin', old('fecha_fin'), ['class' => 'form-control datepicker-no-back', 'placeholder' => 'Ingrese fecha de finalización']) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="message"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="guardar-planificacion">Guardar</button>
            </div>
        </div>
    </div>
</div>
