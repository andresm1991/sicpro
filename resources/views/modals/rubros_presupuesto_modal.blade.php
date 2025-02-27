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
                    {{ Form::hidden('rubro_presupuesto_id') }}
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Categoría', ['class' => 'col-form-label']) }}
                                <select name="categoria_rubro" id="categoria" class="form-control select2-tag",
                                    data-placeholder ='seleccione opción'>
                                    <option value=""></option>
                                    @foreach ($categorias_presupuesto as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
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
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Etapa', ['class' => 'col-form-label']) }}
                                <select name="etapa_construccion" id="etapa_construccion"
                                    class="form-control select2-basic-single", data-placeholder ='seleccione opción'>
                                    <option value=""></option>
                                    @foreach ($etapas_construccion as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Unidad Media', ['class' => 'col-form-label']) }}
                                <select name="unidad_medida" id="unidad_medida" class="form-control select2-tag",
                                    data-placeholder ='seleccione opción'>
                                    <option value=""></option>
                                    @foreach ($unidades_medidas as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Cantidad', ['class' => 'col-form-label']) }}
                                {{ Form::text('cantidad', '', ['class' => 'form-control input-double', 'placeholder' => '0']) }}
                            </div>
                        </div>

                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                {{ Form::label('', 'Costo', ['class' => 'col-form-label']) }}
                                {{ Form::text('valor', '', ['class' => 'form-control money text-right', 'placeholder' => '0']) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="message"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" id="guardar">Agregar</button>
            </div>
        </div>
    </div>
</div>
