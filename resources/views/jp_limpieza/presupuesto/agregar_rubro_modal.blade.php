<!-- Modal -->
<div class="modal fade" id="modalRubrosPresupuesto" tabindex="-1" role="dialog" aria-hidden="true">
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
                <h5 class="modal-title font-weight-bold text-dark" id="titleModal">Agregar Rubro</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete = "off" enctype = "multipart/form-data" id = "form_rubros_presupuesto">
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
                                <select name="categoria" class="form-control select2-tag" id="categoria"
                                    data-placeholder="seleccione opción">
                                    <option value=""></option>
                                    @foreach (getCategoriasPresupuesto() as $item)
                                        <option value="{{ $item->id }}" data-detalle="{{ $item->detalle }}">
                                            {{ $item->descripcion }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'detalle', ['class' => 'col-form-label']) }}
                                {{ Form::text('detalle', '', ['class' => 'form-control', 'id' => 'detalle', 'placeholder' => 'detalle']) }}
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Rubro', ['class' => 'col-form-label']) }}
                                {{ Form::select('rubro', [], null, ['class' => 'form-control select2-tag', 'id' => 'rubros', 'data-placeholder' => 'seleccione opción']) }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                                {{ Form::text('cantidad', '', ['class' => 'form-control input-double', 'id' => 'cantidad', 'placeholder' => '0']) }}
                            </div>
                        </div>

                        <div class="col-md-2 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Valor Unit.', ['class' => 'col-form-label']) }}
                                {{ Form::text('valor', '', ['class' => 'form-control money text-right', 'id' => 'precio_unitario', 'placeholder' => '0']) }}
                            </div>
                        </div>
                        <div class="col-md-2 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Nro. Meses', ['class' => 'col-form-label']) }}
                                {{ Form::text('meses', '', ['class' => 'form-control input-enteros', 'id' => 'meses', 'placeholder' => '0']) }}
                            </div>
                        </div>

                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Total', ['class' => 'col-form-label']) }}
                                {{ Form::label('', '$ 0.0000', ['class' => 'form-control', 'id' => 'total']) }}
                            </div>
                        </div>
                    </div>


                    <div class="form-group" id="message"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="agergar-rubro">Agregar</button>
            </div>
        </div>
    </div>
</div>
