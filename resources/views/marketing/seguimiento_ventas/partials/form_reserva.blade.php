<form id="form_reserva" autocomplete="off" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6 col-12 d-flex justify-content-start align-items-center mb-3">
            {{-- Botón para guardar la información de la reserva --}}
            {{-- Este botón no envía el formulario automáticamente, sino que se maneja con JavaScript --}}
            <button type="button" class="btn btn-dark btn-block col-sm-2" id="guardar-info-reserva">Guardar</button>
        </div>
        <div class="col-sm-6 col-12 d-flex justify-content-end align-items-center">
            <label for="forCheckbox" class="col-form-label mr-3">Etapa completada</label>
            <div class="checkbox-wrapper-8">
                <input class="tgl tgl-skewed" name="etapa_reserva_completa" id="etapa-reserva-completa"
                    type="checkbox" />
                <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="etapa-reserva-completa"></label>
            </div>
        </div>

        <div class="col-md-8 col-12">
            <legend class="custom-legend"><span>
                    Información General
                </span>
            </legend>
            <fieldset class="custom-fieldset">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('documentacion_inicial', 'Documento de identidad *') !!}
                            {!! Form::text(
                                'documento_identidad',
                                isset($seguimientoVenta->cliente) ? $seguimientoVenta->cliente->documento : '',
                                [
                                    'class' => 'form-control solo-numeros',
                                    'id' => 'documento-identidad',
                                    'placeholder' => 'Ingrese el número de documento',
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('nombre', 'Nombre del Cliente *') !!}
                            {!! Form::text('nombre', isset($seguimientoVenta->cliente) ? $seguimientoVenta->cliente->nombre : '', [
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el nombre del cliente',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('telefono', 'Teléfono') !!}
                            {!! Form::text('telefono', isset($seguimientoVenta->cliente) ? $seguimientoVenta->cliente->telefono : '', [
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el teléfono',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('email', 'Email') !!}
                            {!! Form::email('email', isset($seguimientoVenta->cliente) ? $seguimientoVenta->cliente->email : '', [
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el email',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('direccion', 'Dirección') !!}
                            {!! Form::text('direccion', isset($seguimientoVenta->cliente) ? $seguimientoVenta->cliente->direccion : '', [
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese la dirección',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('valor_reserva', 'Valor de Reserva *') !!}
                            {!! Form::text('valor_reserva', $seguimientoVenta->valor_reserva, [
                                'class' => 'form-control moneyDosDecimales',
                                'placeholder' => '$ 0.00',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            {!! Form::label('observaciones', 'Observaciones') !!}
                            {!! Form::textarea(
                                'observaciones',
                                isset($seguimientoVenta->cliente) ? $seguimientoVenta->cliente->observaciones : '',
                                [
                                    'class' => 'form-control',
                                    'placeholder' => 'Ingrese las observaciones',
                                    'rows' => 3,
                                ],
                            ) !!}
                        </div>
                    </div>
                </div>
            </fieldset>
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
                            @isset($seguimientoVenta->contrato_firmado_path)
                                <a href="{{ doTemporaryUrl($seguimientoVenta->contrato_firmado_path) }}" target="_blank"
                                    class="badge badge-success"><i class="fa-solid fa-eye"></i></a>
                            @endisset
                        </label>

                        <div class="custom-file">
                            <input type="file" name="file_contrato_firmado" class="custom-file-input" id="contrato"
                                lang="es">
                            <label class="custom-file-label"
                                for="contrato">{{ $seguimientoVenta->contrato_firmado_path ? basename($seguimientoVenta->contrato_firmado_path) : 'Seleccionar archivo...' }}</label>
                        </div>
                    </div>

                    <div class="form-group col-12">
                        <label for="comprobante_reserva">Comprobante de Reserva
                            @isset($seguimientoVenta->comprobante_pago_reserva_path)
                                <a href="{{ doTemporaryUrl($seguimientoVenta->comprobante_pago_reserva_path) }}"
                                    target="_blank" class="badge badge-success"><i class="fa-solid fa-eye"></i></a>
                            @endisset
                        </label>

                        <div class="custom-file">
                            <input type="file" name="fila_comprobante_pago_reserva" class="custom-file-input"
                                id="comprobante_reserva" lang="es">
                            <label class="custom-file-label"
                                for="comprobante_reserva">{{ $seguimientoVenta->comprobante_pago_reserva_path ? basename($seguimientoVenta->comprobante_pago_reserva_path) : 'Seleccionar archivo...' }}</label>
                        </div>
                    </div>

                    <div class="form-group col-12">
                        <label for="documento_identidad">Documento de Identidad
                            @isset($seguimientoVenta->cedulas_path)
                                <a href="{{ doTemporaryUrl($seguimientoVenta->cedulas_path) }}" target="_blank"
                                    class="badge badge-success"><i class="fa-solid fa-eye"></i></a>
                            @endisset
                        </label>
                        <div class="custom-file">
                            <input name="documento_identidad" type="file" class="custom-file-input"
                                id="documento_identidad" lang="es">
                            <label class="custom-file-label"
                                for="documento_identidad">{{ $seguimientoVenta->cedulas_path ? basename($seguimientoVenta->cedulas_path) : 'Seleccionar archivo...' }}</label>
                        </div>
                    </div>

                </div>
                <div class="form-group">

                    <small class="form-text text-muted">Tipos de archivo permitidos: pdf, doc, docx, jpg, jpeg,
                        png.</small>
                </div>
            </fieldset>
        </div>

        <div class="col-12">
            <div class="form-group">
                <label for="contenido" class="col-form-label">Contenido del Contrato
                    @isset($seguimientoVenta->contrato)
                        <br>
                        <a href="{{ route('marketing.seguimiento.ventas.contrato.preview', $seguimientoVenta->contrato->id) }}"
                            class="btn btn-sm btn-dark " target="_blank">
                            <i class="fa-solid fa-eye mr-2"></i> Vista Previa
                        </a>
                    @endisset
                </label>

                {{-- Precargamos el textarea con el HTML de nuestra plantilla --}}
                <textarea id="summernote" name="contenido">
        {!! $plantillaContenido !!}
    </textarea>
            </div>
        </div>
    </div>
</form>
