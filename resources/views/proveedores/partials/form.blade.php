<div class="form-row">
    <div class="form-group col-md-6">
        <label class="col-form-label">RUC | CI <i class="fa-regular fa-asterisk fa-2xs"></i></label>
        {{ Form::text('documento', old('documento', $proveedor->documento), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese RUC o CI', 'maxlength' => '13']) }}
        {!! $errors->first('documento', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="form-group col-md-6">
        <label class="col-form-label">razón social {!! $slug != 'mano.obra' ? '<i class="fa-regular fa-asterisk fa-2xs"></i>' : '' !!}</label>
        {{ Form::text('razon_social', old('razon_social', $proveedor->razon_social), ['class' => 'form-control', 'placeholder' => 'Ingrese razón social']) }}
        {!! $errors->first('documento', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="col-form-label">Nombres <i class="fa-regular fa-asterisk fa-2xs"></i></label>
        {{ Form::text('nombres', old('nombres', $proveedor->nombres), ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre']) }}
        {!! $errors->first('nombres', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="form-group col-md-6">
        <label class="col-form-label">Apellidos <i class="fa-regular fa-asterisk fa-2xs"></i></label>
        {{ Form::text('apellidos', old('apellidos', $proveedor->apellidos), ['class' => 'form-control', 'placeholder' => 'Ingrese el apellido']) }}
        {!! $errors->first('apellidos', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>


@include('proveedores.partials.telefono')

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="col-form-label">correo electrónico {!! $slug != 'mano.obra' ? '<i class="fa-regular fa-asterisk fa-2xs"></i>' : '' !!}</label>
        {{ Form::text('email', old('email', $proveedor->correo), ['class' => 'form-control', 'placeholder' => 'Ingrese dirección de correo electrónico']) }}
        {!! $errors->first('email', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="form-group col-md-6">
        <label for="inputDireccion" class="col-form-label">Dirección <i
                class="fa-regular fa-asterisk fa-2xs"></i></label>
        {{ Form::text('direccion', old('direccion', $proveedor->direccion), ['class' => 'form-control', 'placeholder' => 'Ingrese dirección']) }}
        {!! $errors->first('direccion', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

<div class="form-row">
    <label class="col-sm-12 col-form-label">Datos Bancario</label>
    <div class="form-group col-md-4">
        {{ Form::select('banco', $bancos->prepend('', ''), old('banco', $proveedor->banco_id), ['class' => 'form-control', 'data-placeholder' => 'Seleccione el Banco']) }}

        {!! $errors->first('banco', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="form-group col-md-4">
        {{ Form::text('numero_cuenta', old('numero_cuenta', $proveedor->numero_cuenta), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese numero de cuenta']) }}
        {!! $errors->first('numero_cuenta', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="form-group col-md-4">
        {{ Form::select('tipo_cuenta', $tipo_cuenta->prepend('', ''), old('tipo_cuenta', $proveedor->tipo_cuenta_id), ['class' => 'form-control', 'data-placeholder' => 'Seleccione el tipo de cuenta']) }}
        {!! $errors->first('tipo_cuenta', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

@include('proveedores.partials.productos_select_option')

<div class="form-group">
    <label class="col-form-label">Observación</label>
    {!! Form::textarea('observacion', old('observacion', $proveedor->observacion), [
        'class' => 'form-control',
        'id' => 'observacion',
        'rows' => 4,
        'placeholder' => 'Ingrese las observaciones (opcional)',
    ]) !!}
</div>
