<div class="form-row">
    <div class="form-group col-md-6">
        <label for="inputNombres" class="font-weight-bold">Nombres *</label>
        {{ Form::text('nombres', old('nombres', $cliente->nombre), ['class' => 'form-control', 'id' => 'inputNombres', 'placeholder' => 'Ingrese nombres']) }}
        {!! $errors->first('nombres', '<small class="help-block text-danger">:message</small>') !!}
    </div>
    <div class="form-group col-md-6">
        <label for="inputDNI" class="font-weight-bold">Identificación</label>
        {{ Form::text('identificacion', old('identificacion', $cliente->ruc), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese identificación', 'maxlength' => 13]) }}
        {!! $errors->first('identificacion', '<small class="help-block text-danger">:message</small>') !!}
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="inputCiudad" class="font-weight-bold">Ciudad</label>
        {{ Form::select('ciudad', getCiudades(), old('ciudad', $cliente->ciudad), ['class' => 'form-control select2-tag', 'data-placeholder' => 'Seleccione una ciudad']) }}
        {!! $errors->first('ciudad', '<small class="help-block text-danger">:message</small>') !!}
    </div>
    <div class="form-group col-md-4">
        <label for="inputCorreo" class="font-weight-bold">Correo</label>
        {{ Form::email('correo', old('correo', $cliente->correo), ['class' => 'form-control', 'id' => 'inputCorreo', 'placeholder' => 'Ingrese correo']) }}
        {!! $errors->first('correo', '<small class="help-block text-danger">:message</small>') !!}
    </div>
    <div class="form-group col-md-2">
        <label for="inputTelefono" class="font-weight-bold">Teléfono</label>
        {{ Form::text('telefono', old('telefono', $cliente->telefono), ['class' => 'form-control input-enteros', 'id' => 'inputTelefono', 'placeholder' => 'Ingrese teléfono']) }}
        {!! $errors->first('telefono', '<small class="help-block text-danger">:message</small>') !!}
    </div>
</div>

<div class="form-group">
    <label for="inputDireccion" class="font-weight-bold">Dirección</label>
    {{ Form::text('direccion', old('direccion', $cliente->direccion), ['class' => 'form-control', 'id' => 'inputDireccion', 'placeholder' => 'Ingrese dirección']) }}
    {!! $errors->first('direccion', '<small class="help-block text-danger">:message</small>') !!}
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="inputContacto" class="font-weight-bold">Nombre Contacto</label>
        {{ Form::text('contacto', old('contacto', $cliente->contacto), ['class' => 'form-control', 'id' => 'inputContacto', 'placeholder' => 'Ingrese nombre de contacto']) }}
        {!! $errors->first('contacto', '<small class="help-block text-danger">:message</small>') !!}
    </div>
    <div class="form-group col-md-4">
        <label for="inputEmailContacto" class="font-weight-bold">Correo Contacto</label>
        {{ Form::email('correo_contacto', old('correo_contacto', $cliente->correo_contacto), ['class' => 'form-control', 'id' => 'inputCorreo', 'placeholder' => 'Ingrese correo']) }}
        {!! $errors->first('correo_contacto', '<small class="help-block text-danger">:message</small>') !!}
    </div>
    <div class="form-group col-md-2">
        <label for="inputTlfContacto" class="font-weight-bold">Teléfono Contacto</label>
        {{ Form::text('telefono_contacto', old('telefono_contacto', $cliente->telefono_contacto), ['class' => 'form-control input-enteros', 'id' => 'inputTlfContacto', 'placeholder' => 'Ingrese Teléfono']) }}
        {!! $errors->first('telefono_contacto', '<small class="help-block text-danger">:message</small>') !!}
    </div>

</div>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" name="activo" value="true" type="checkbox" id="gridCheck"
            {{ $cliente->activo ? 'checked' : '' }}>
        <label class="form-check-label font-weight-bold" for="gridCheck">
            Activo
        </label>
    </div>
</div>
