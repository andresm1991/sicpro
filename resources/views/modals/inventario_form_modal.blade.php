<!-- Modal -->
<div class="modal fade" id="inventarioFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->

            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary" id="titleFormModal">Nuevo Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="from-articulo">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-form-label">Producto <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                        {{ Form::select('producto', $productos, null, ['class' => 'form-control select2-basic-single col-12', 'data-placeholder' => 'Seelcione el producto']) }}
                    </div>

                    <div class="row">
                        <div class="col-sm-4 col-12">
                            <div class="form-group">
                                <label class="col-form-label">Cantidad <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <input type="text" name="cantidad" class="form-control input-enteros"
                                    placeholder="0">
                            </div>
                        </div>
                        <div class="col-sm-8 col-12">
                            <div class="form-group">
                                <label class="col-form-label" for="estado">Selecciona el estado (1-10) <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <div class="btn-group btn-group-toggle btn-group-sm mt-1" data-toggle="buttons">
                                    @for ($i = 0; $i < 10; $i++)
                                        <label class="btn btn-outline-dark">
                                            <input type="radio" name="estado" id="estado{{ $i + 1 }}"
                                                value="{{ $i + 1 }}"> {{ $i + 1 }}
                                        </label>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 p-0">
                        <p class="font-italic">Los campos con (*) son obligatorios, por favor complétalos corretéame</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-dark" id="guardar">
                        <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        Guardar</button>

                    <div class="col-12 alert alert-dismissible fade show d-none" role="alert" id="message">
                        <strong></strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
