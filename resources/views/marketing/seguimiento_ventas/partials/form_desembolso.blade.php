<form id="form_desembolso"
    action="{{ isset($seguimientoVenta->id) ? route('marketing.seguimiento.ventas.etapa.desembolso', $seguimientoVenta) : '' }}"
    autocomplete="off" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6 col-12 d-flex justify-content-start align-items-center mb-3">
            <button type="submit" class="btn btn-dark btn-block col-sm-2">Guardar</button>
        </div>
        <div class="col-sm-6 col-12 d-flex justify-content-end align-items-center">
            <label for="forCheckbox" class="col-form-label mr-3">Etapa completada</label>
            <div class="checkbox-wrapper-8">
                <input class="tgl tgl-skewed" name="etapa_desembolso_completa" id="etapa-desembolso-completa"
                    type="checkbox" value="1" />
                <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="etapa-desembolso-completa"></label>
            </div>
        </div>

        <div class="col-12">
            <ul class="list-group mb-3" id="lista-desembolso">

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="col-sm-10 col-12">
                        <span class="font-weight-bold">Dinero acreditado</span>
                        <small class="text-muted d-block"></small>
                    </div>

                    <div class="col-sm-2 col-12 d-flex justify-content-end">

                        <div class="form-group mb-0">
                            <input type="text" name="valor"
                                value="{{ number_format($seguimientoVenta->monto_desembolsado, 2) }}"
                                class="form-control moneyDosDecimales" placeholder="$ 0.00">
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="col-12">
            <div class="form-group">
                <label for="forObservaciones" class="col-form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" id="forObservaciones" rows="3"
                    placeholder="ingrese las observaciones presentadas">{{ $seguimientoVenta->observaciones_desembolso }}</textarea>
            </div>
        </div>

    </div>
</form>
