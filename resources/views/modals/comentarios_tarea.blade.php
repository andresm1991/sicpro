<!-- Modal -->
<div class="modal fade" id="comentarioTareaModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                <div class="container">
                    <div class="row">
                        <div class="col-sm-8 p-0">
                            <h5 class="modal-title font-weight-bold text-dark" id="titleComentarioModal"></h5>
                        </div>

                        <div class="col-sm-auto">
                            <label class="col-form-label">Estado</label>
                        </div>
                        <div class="col-sm-2">
                            <select name="estado" data-tags="false" data-placeholder="Select an option"
                                data-allow-clear="false" class="form-control" data-width = "150"
                                data-minimum-results-for-search="Infinity">
                                @foreach ($estados as $index => $name)
                                    <option value="{{ $index }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>



                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" enctype="multipart/form-data" id="form_comentario_tarea">
                    {{ Form::hidden('tarea_id') }}
                    <div class="row">
                        <div class="col-12 form-group">
                            <label id="descripcion">Desacripción</label>
                        </div>

                        <div class="col-12 form-group">
                            {{ Form::label('', 'Colaboradores', ['class' => 'col-form-label']) }}
                            {{ Form::select('list_usuarios[]', usuariosPluck(), 0, ['class' => 'select2-basic-single form-control', 'id' => 'list_usuarios', 'multiple' => 'multiple', 'data-placeholder' => 'SELECCIONE USUARIOS']) }}
                        </div>

                        <div class="col-12 form-group">
                            <label class="col-form-label">Categoria</label>
                            {{ Form::select('categoria_tarea', categoriasAgenda(), '', ['class' => 'select2-tag form-control', 'id' => 'categoria_tarea', 'data-placeholder' => 'SELECCIONE OPCIÓN']) }}
                        </div>

                        <div class="col-12 form-group">
                            {{ Form::label('', 'Comentarios', ['class' => 'col-form-label']) }}
                            {{ Form::textarea('comentario', '', ['class' => 'form-control', 'id' => 'comentario', 'placeholder' => 'Añadir un comentario...', 'rows' => '3']) }}
                        </div>
                    </div>

                    <button type="button" class="btn btn-dark mb-4" id="guardar-comentario">Agregar</button>
                </form>
                <div class="row">
                    <div class="col-12" id="comentarios">

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
