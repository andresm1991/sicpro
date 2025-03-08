<!-- Modal -->
<div class="modal fade" id="pagoSemanalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x"></i>
                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Registro</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" enctype="multipart/form-data" id="form_resumen_pagos_semanales">
                    {{ Form::hidden('resumen_id') }}
                    <div class="row mb-2">
                        <div class="col-12 form-group">
                            <label class="col-form-label">Fecha registro: </label>
                            <label id="fecha_registro">{{ date('Y-m-d') }}</label>
                        </div>



                        <div class="col-12">
                            <div class="form-row align-items-center">
                                <div class="form-group col-md-6">
                                    <label class="col-form-label">Descripción</label>
                                    {{ Form::select('descripcion', pluckDescripcionesResumenPagosSemanales(), '', ['class' => 'select2-tag', 'data-placeholder' => 'Seleccione o ingrese descripción']) }}
                                </div>

                                <div class="form-group col-md-2 col-12">
                                    <label class="col-form-label">Valor</label>
                                    {{ Form::text('valor', '', ['class' => 'form-control currency_separador_miles']) }}
                                </div>

                                <div class="col-md-2 col-12 d-flex align-items-center mt-4">
                                    <button type="button" class="btn btn-dark" id="agregar-item">Agregar</button>
                                </div>
                                <div class="col-12">
                                    <div class="select_wrapper">
                                        <label class="rounded  text-white">
                                            <input type="checkbox" name="completado" class="d-none" value="true">
                                            <span class="text-center d-block py-3">
                                                Completado
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabla_items">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Item</th>
                                    <th scope="col">Total</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="tr-default">
                                    <td colspan="4" class="aling-middle text-center">No existen elemento en al lista
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-10 text-right align-self-center font-weight-bold">
                            Total General
                        </div>
                        <div class="col-2">
                            <input type="text" class="form-control-plaintext" id="total_general" value="$ 0.0000"
                                readonly>
                        </div>
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
