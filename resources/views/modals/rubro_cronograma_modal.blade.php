<!-- Modal -->
<div class="modal fade" id="modalRubroCronograma" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content card-outline card-primary">
            <!-- Overlay -->
            <div id="modal-overlay" style="display:none;">
                <div class="overlay-content">
                    <i class="fa-solid fa-rotate fa-spin fa-5x"></i>

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
                <form autocomplete="off" enctype="multipart/form-data" id="form_rubro_cronograma">
                    <div class="row">
                        @php
                            // Array con los días de la semana
                            $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                        @endphp

                        <div class="col-sm-8 col-12">
                            <div class="from-group">
                                <label class="font-weight-bold">Rubros Cronograma</label>
                                {!! Form::select('rubro_cronograma', $rubros_cronograma->prepend('', ''), null, [
                                    'class' => 'form-control  select2-tag',
                                    'data-placeholder' => 'Selecciona o ingresa el rubro',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-sm-4 col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Semana</label>
                                <select name="semana" id="semana" class="form-control select2-basic-single"
                                    data-placeholder="Selecione semana">
                                    <option value=""></option>
                                    @for ($i = 0; $i < $plazo_semanas; $i++)
                                        <option value="{{ $i + 1 }}">{{ 'semana ' . $i + 1 }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <tbody>
                                    @foreach ($dias as $dia)
                                        <tr>
                                            <td class="align-middle font-weight-bold">
                                                <div class="form-check">
                                                    <!-- Checkbox con nombre estructurado -->
                                                    <input name="dias[{{ $dia }}][checked]"
                                                        class="form-check-input" type="checkbox" value="1"
                                                        id="check_{{ $dia }}">
                                                    <label class="form-check-label" for="check_{{ $dia }}">
                                                        {{ $dia }}
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <!-- Campo de texto con nombre estructurado -->
                                                {{ Form::text("dias[$dia][observacion]", null, ['class' => 'form-control', 'placeholder' => 'Ingresa la observación (opcional)']) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
