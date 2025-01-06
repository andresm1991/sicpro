<!-- Modal -->
<div class="modal fade" id="modalPrestamo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x" style="color: rgba(0, 0, 0, 0.5);"></i>

                    <p>Procesando...</p>
                </div>
            </div>
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Nuevo Prestamo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete = "off" enctype = "multipart/form-data" id = "form_prestamo">
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Trabajador', ['class' => 'col-form-label']) }}
                                {{ Form::select('proveedor', [], null, ['class' => 'select2-basic-single form-control', 'id' => 'proveedores', 'data-placeholder' => 'seleccione trabajador']) }}
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Fecha Solicitud', ['class' => 'col-form-label']) }}
                                {{ Form::text('fecha_solicitud', date('Y-m-d'), ['class' => 'form-control', 'readonly']) }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Monto', ['class' => 'col-form-label']) }}
                                {{ Form::text('monto', '', ['class' => 'form-control currency_two_decimals', 'placeholder' => '0']) }}
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Plazo Semanas', ['class' => 'col-form-label']) }}
                                {{ Form::text('plazo', '', ['class' => 'form-control input-enteros', 'placeholder' => '0']) }}
                            </div>
                        </div>
                        <div class="col-md-2 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Interes', ['class' => 'col-form-label']) }}
                                {{ Form::text('interes', '', ['class' => 'form-control input-enteros', 'placeholder' => '0']) }}
                            </div>
                        </div>

                        <div class="col-md-5 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Estado', ['class' => 'col-form-label']) }}
                                {{ Form::select('estado', [], null, ['class' => 'form-control select2-basic-single', 'id' => 'estados', 'data-placeholder' => 'seleccione opción']) }}
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Detalle', ['class' => 'col-form-label']) }}
                                {{ Form::textarea('motivo', '', ['class' => 'form-control', 'placeholder' => 'Ingrese una descripción del prestamo', 'rows' => 4]) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="message"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="guardar">Guardar</button>
            </div>
        </div>
    </div>
</div>
