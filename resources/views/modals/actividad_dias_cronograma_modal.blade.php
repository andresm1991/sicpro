<!-- Modal -->
<div class="modal fade" id="modalActividadDiasCronograma" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x" style="color: rgba(0, 0, 0, 0.5);"></i>

                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Actividades por días</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" enctype="multipart/form-data" id="form_rubros_presupuesto">
                    <div class="row">
                        @php
                            // Array con los días de la semana
                            $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                        @endphp
            
                        @foreach ($dias as $dia)
                            <div class="col-sm-2 day-item">
                                <label class="font-weight-bold">{{ ucfirst($dia) }}</label>
                                <div class="fields-container form-group" id="{{ $dia }}-fields">
                                    <select name="{{ $dia }}[]" class="form-control actividades" data-placeholder="Selecciona actividad"></select>
                                </div>
                                <button type="button" class="btn btn-success btn-sm add-field" data-day="{{ $dia }}">
                                    <i class="fa-solid fa-plus"></i> Agregar
                                </button>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="guardar">Agregar</button>
            </div>
        </div>
    </div>
</div>
