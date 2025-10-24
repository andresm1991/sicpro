<form id="form_acta_entrega"
    action="{{ isset($seguimientoVenta->id) ? route('marketing.seguimiento.ventas.etapa.entrega', $seguimientoVenta) : '' }}"
    autocomplete="off" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6 col-12 d-flex justify-content-start align-items-center mb-3">
            {{-- Botón para guardar la información de la entrega --}}
            {{-- Este botón no envía el formulario automáticamente, sino que se maneja con JavaScript --}}
            <button type="submit" class="btn btn-dark btn-block col-sm-2">Guardar</button>
        </div>
        <div class="col-sm-6 col-12 d-flex justify-content-end align-items-center">
            <label for="forCheckbox" class="col-form-label mr-3">Etapa completada</label>
            <div class="checkbox-wrapper-8">
                <input class="tgl tgl-skewed" name="etapa_entrega_completa" id="etapa-entrega-completa" type="checkbox"
                    @checked($seguimientoVenta->isEtapaCompleta('Entrega')) />
                <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="etapa-entrega-completa"></label>
            </div>
        </div>
        <div class="col-sm-8 col-12">
            <div class="form-group">
                <label for="contenido" class="col-form-label">Contenido del Acta de Entrega
                    @if ($seguimientoVenta->contrato->where('titulo', 'acta de entrega')->first())
                        <br>
                        <a href="{{ route('marketing.seguimiento.ventas.contrato.preview', $seguimientoVenta->contrato->where('titulo', 'acta de entrega')->first()->id) }}"
                            class="btn btn-sm btn-dark " target="_blank">
                            <i class="fa-solid fa-eye mr-2"></i> Vista Previa
                        </a>
                    @endif
                </label>
                {{ Form::textarea('contenido_acta_entrega', $plantillaActaEntrega ?? old('contenido'), ['class' => 'form-control summernote', 'id' => 'contenido']) }}
            </div>
        </div>

        <div class="col-md-4 col-12">
            <legend class="custom-legend"><span>
                    Archivos
                </span>
            </legend>
            <fieldset class="custom-fieldset">

                <div class="row">
                    <div class="form-group col-12">
                        <label for="contrato">Contrato firmado
                            @isset($seguimientoVenta->contrato_entrega_firmado_path)
                                <a href="{{ doTemporaryUrl($seguimientoVenta->contrato_entrega_firmado_path) }}"
                                    target="_blank" class="badge badge-success"><i class="fa-solid fa-eye"></i></a>
                            @endisset
                        </label>

                        <div class="custom-file">
                            <input type="file" name="file_contrato_entrega_firmado" class="custom-file-input"
                                id="contrato_entrega" lang="es">
                            <label class="custom-file-label"
                                for="contrato_entrega">{{ $seguimientoVenta->contrato_entrega_firmado_path ? basename($seguimientoVenta->contrato_entrega_firmado_path) : 'Seleccionar archivo...' }}</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">

                    <small class="form-text text-muted">Tipos de archivo permitidos: pdf, doc, docx, jpg, jpeg,
                        png.</small>
                </div>
            </fieldset>
        </div>
    </div>
</form>
@section('scripts')
    <script src="{{ asset('js/proceso_ventas.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection
