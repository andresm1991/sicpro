<form id="form_peritaje"
    action="{{ isset($seguimientoVenta->id) ? route('marketing.seguimiento.ventas.etapa.peritaje.aprobacion', $seguimientoVenta) : '' }}"
    autocomplete="off" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6 col-12 d-flex justify-content-start align-items-center mb-3">
            <button type="submit" class="btn btn-dark btn-block col-sm-2">Guardar</button>
        </div>
        <div class="col-sm-6 col-12 d-flex justify-content-end align-items-center">
            <label for="forCheckbox" class="col-form-label mr-3">Etapa completada</label>
            <div class="checkbox-wrapper-8">
                <input class="tgl tgl-skewed" name="etapa_peritaje_completa" id="etapa-peritaje-completa"
                    type="checkbox" value="1" />
                <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="etapa-peritaje-completa"></label>
            </div>
        </div>

        <div class="col-12">
            <ul class="list-group mb-3" id="lista-peritaje">

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="col-sm-10 col-12">
                        <span class="font-weight-bold">visita del perito</span>
                        <small class="text-muted d-block"></small>
                    </div>

                    <div class="col-sm-2 col-12 d-flex justify-content-end">
                        <div class="checkbox-wrapper-8">
                            <input class="tgl tgl-skewed" name="visita_peritaje" id="visita-perito-completa"
                                type="checkbox" value="1"
                                {{ $seguimientoVenta->visita_perito ? 'checked' : '' }} />
                            <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI"
                                for="visita-perito-completa"></label>
                        </div>
                    </div>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="col-sm-10 col-12">
                        <span class="font-weight-bold">Aprobacion del credito</span>
                        <small class="text-muted d-block"></small>
                    </div>

                    <div class="col-sm-2 col-12 d-flex justify-content-end">
                        <div class="checkbox-wrapper-8">
                            <input class="tgl tgl-skewed" name="aprobacion_credito" id="aprobacion-credito-completa"
                                type="checkbox" value="1"
                                {{ $seguimientoVenta->aprobacion_credito ? 'checked' : '' }} />
                            <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI"
                                for="aprobacion-credito-completa"></label>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="col-12">
            <div class="form-group">
                <label for="forObservaciones" class="col-form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" id="forObservaciones" rows="3"
                    placeholder="ingrese las observaciones presentadas">{{ $seguimientoVenta->observaciones_peritaje }}</textarea>
            </div>
        </div>

    </div>
</form>
