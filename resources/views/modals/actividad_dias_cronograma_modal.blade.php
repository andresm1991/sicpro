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
                <form autocomplete = "off" enctype = "multipart/form-data" id = "form_rubros_presupuesto">
                    <div class="row">
                        <!-- Días de la semana -->
                        <div class="col-sm-2 day-item">
                            <label class="font-weight-bold">Lunes</label>
                            <div class="fields-container" id="lunes-fields">
                                <input type="text" name="lunes[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-field" data-day="lunes"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
                        <div class="col-sm-2 day-item">
                            <label class="font-weight-bold">Martes</label>
                            <div class="fields-container" id="martes-fields">
                                <input type="text" name="martes[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-field" data-day="martes"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
                        <div class="col-sm-2 day-item">
                            <label class="font-weight-bold">Miércoles</label>
                            <div class="fields-container" id="miercoles-fields">
                                <input type="text" name="miercoles[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-field" data-day="miercoles"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
                        <div class="col-sm-2 day-item">
                            <label class="font-weight-bold">Jueves</label>
                            <div class="fields-container" id="jueves-fields">
                                <input type="text" name="jueves[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-field" data-day="jueves"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
                        <div class="col-sm-2 day-item">
                            <label class="font-weight-bold">Viernes</label>
                            <div class="fields-container" id="viernes-fields">
                                <input type="text" name="viernes[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-field" data-day="viernes"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
                        <div class="col-sm-2 day-item">
                            <label class="font-weight-bold">Sábado</label>
                            <div class="fields-container" id="sabado-fields">
                                <input type="text" name="sabado[]" class="form-control mb-2">
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-field" data-day="sabado"><i class="fa-solid fa-plus"></i> Agregar</button>
                        </div>
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
