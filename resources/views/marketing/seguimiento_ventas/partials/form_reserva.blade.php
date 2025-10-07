<form id="form_reserva" autocomplete="off" enctype="multipart/form-data">
    <div class="row">
        <div class="col-3">
            <div class="list-group" id="list-tab" role="tablist">
                <a class="list-group-item list-group-item-action active" id="informacion-form" data-toggle="list"
                    href="#informacion" role="tab" aria-controls="informacion">Información general</a>
                <a class="list-group-item list-group-item-action" id="list-profile-list" data-toggle="list"
                    href="#list-profile" role="tab" aria-controls="profile">Contrato</a>
                <a class="list-group-item list-group-item-action" id="list-messages-list" data-toggle="list"
                    href="#list-messages" role="tab" aria-controls="messages">Adjuntos</a>
            </div>
        </div>
        <div class="col-9">
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="informacion" role="tabpanel"
                    aria-labelledby="informacion-form">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {!! Form::label('nombre', 'Nombre del Cliente *') !!}
                                {!! Form::text('nombre', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del cliente']) !!}
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {!! Form::label('documentacion_inicial', 'Documento de identidad *') !!}
                                {!! Form::text('documento_identidad', null, [
                                    'class' => 'form-control solo-numeros',
                                    'placeholder' => 'Ingrese el número de documento',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {!! Form::label('telefono', 'Teléfono') !!}
                                {!! Form::text('telefono', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el teléfono']) !!}
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {!! Form::label('email', 'Email') !!}
                                {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el email']) !!}
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {!! Form::label('direccion', 'Dirección') !!}
                                {!! Form::text('direccion', null, ['class' => 'form-control', 'placeholder' => 'Ingrese la dirección']) !!}
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                {!! Form::label('valor_reserva', 'Valor de Reserva *') !!}
                                {!! Form::text('valor_reserva', null, [
                                    'class' => 'form-control moneyDosDecimales',
                                    'placeholder' => '$ 0.00',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                {!! Form::label('observaciones', 'Observaciones') !!}
                                {!! Form::textarea('observaciones', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Ingrese las observaciones',
                                    'rows' => 3,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">...
                </div>
                <div class="tab-pane fade" id="list-messages" role="tabpanel" aria-labelledby="list-messages-list">...
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-dark btn-block col-sm-2" id="guardar-info-reserva">Guardar</button>
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
                            {!! Form::label('nombre', 'Nombre del Cliente *') !!}
                            {!! Form::text('nombre', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del cliente']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('documentacion_inicial', 'Documento de identidad *') !!}
                            {!! Form::text('documento_identidad', null, [
                                'class' => 'form-control solo-numeros',
                                'placeholder' => 'Ingrese el número de documento',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('telefono', 'Teléfono') !!}
                            {!! Form::text('telefono', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el teléfono']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('email', 'Email') !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el email']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('direccion', 'Dirección') !!}
                            {!! Form::text('direccion', null, ['class' => 'form-control', 'placeholder' => 'Ingrese la dirección']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            {!! Form::label('valor_reserva', 'Valor de Reserva *') !!}
                            {!! Form::text('valor_reserva', null, [
                                'class' => 'form-control moneyDosDecimales',
                                'placeholder' => '$ 0.00',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            {!! Form::label('observaciones', 'Observaciones') !!}
                            {!! Form::textarea('observaciones', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese las observaciones',
                                'rows' => 3,
                            ]) !!}
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
                        {!! Form::label('contrato', 'Contrato firmado') !!}
                        <div class="custom-file">
                            <input type="file" name="file_contrato_firmado" class="custom-file-input" id="contrato"
                                lang="es">
                            <label class="custom-file-label" for="contrato">Seleccionar archivo...</label>
                        </div>
                    </div>

                    <div class="form-group col-12">
                        {!! Form::label('comprobante_reserva', 'Comprobante de Reserva') !!}
                        <div class="custom-file">
                            <input type="file" name="fila_comprobante_pago_reserva" class="custom-file-input"
                                id="comprobante_reserva" lang="es">
                            <label class="custom-file-label" for="comprobante_reserva">Seleccionar archivo...</label>
                        </div>
                    </div>

                    <div class="form-group col-12">
                        {!! Form::label('documento_identidad', 'Documento de Identidad') !!}
                        <div class="custom-file">
                            <input name="documentos_identidad[]" type="file" class="custom-file-input"
                                id="documento_identidad" lang="es" multiple>
                            <label class="custom-file-label" for="documento_identidad">Seleccionar archivo...</label>
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
                <label for="contenido" class="col-form-label">Contenido del Contrato</label>
                {{-- Precargamos el textarea con el HTML de nuestra plantilla --}}
                <textarea id="summernote" name="contenido">
        {!! $plantillaContenido !!}
    </textarea>
            </div>
        </div>
    </div>
</form>
