<div class="form-group row">
    <label for="inputRUC-CI" class="col-sm-2 col-form-label">RUC | CI <i class="fa-regular fa-asterisk fa-2xs"></i></label>
    <div class="col-sm-10">
        {{ Form::text('documento', old('documento', $proveedor->documento), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese RUC o CI', 'maxlength' => '13']) }}
        {!! $errors->first('documento', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

<div class="form-group row">
    <label for="inputNombres" class="col-sm-2 col-form-label">Nombre y Apellido <i
            class="fa-regular fa-asterisk fa-2xs"></i></label>
    <div class="col-sm-10">
        {{ Form::text('nombres', old('nombres', $proveedor->razon_social), ['class' => 'form-control text-capitalize', 'placeholder' => 'Ingrese nombre y apellido']) }}
        {!! $errors->first('nombres', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

<div class="form-group row">
    <label for="inputDireccion" class="col-sm-2 col-form-label">Dirección <i
            class="fa-regular fa-asterisk fa-2xs"></i></label>
    <div class="col-sm-10">
        {{ Form::text('direccion', old('direccion', $proveedor->direccion), ['class' => 'form-control', 'placeholder' => 'Ingrese dirección']) }}
        {!! $errors->first('direccion', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

@include('proveedores.partials.telefono')


<div class="form-group row">
    <label for="inputEmail" class="col-sm-2 col-form-label">Email {!! $slug != 'mano.obra' ? '<i class="fa-regular fa-asterisk fa-2xs"></i>' : '' !!}</label>
    <div class="col-sm-10">
        {{ Form::text('email', old('email', $proveedor->correo), ['class' => 'form-control', 'placeholder' => 'Ingrese dirección de correo electrónico']) }}
        {!! $errors->first('email', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

<div class="form-group row">
    <label for="inputBanco" class="col-sm-2 col-form-label">Datos Bancario</label>
    <div class="col">
        {{ Form::select('banco', $bancos, old('banco', $proveedor->banco_id), ['class' => 'selectpicker show-tick show-menu-arrow form-control border', 'data-live-search' => count($bancos) > 0 ? 'true' : 'false', 'data-style' => '', 'title' => 'Seleccione el Banco', 'data-header' => count($bancos) > 0 ? 'Seleccione opción' : 'No existen datos registrados.']) }}

        {!! $errors->first('banco', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="col">
        {{ Form::text('numero_cuenta', old('numero_cuenta', $proveedor->numero_cuenta), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese numero de cuenta']) }}
        {!! $errors->first('numero_cuenta', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="col">
        {{ Form::select('tipo_cuenta', $tipo_cuenta, old('tipo_cuenta', $proveedor->tipo_cuenta_id), ['class' => 'selectpicker show-tick show-menu-arrow form-control border ', 'data-style' => '', 'title' => 'Seleccione el tipo de cuenta']) }}
        {!! $errors->first('tipo_cuenta', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

@include('proveedores.partials.productos_select_option')

<div class="form-group">
    <label for="name">Observación</label>
    {!! Form::textarea('observacion', old('observacion', $proveedor->observacion), [
        'class' => 'form-control',
        'id' => 'observacion',
        'rows' => 4,
        'placeholder' => 'Ingrese las observaciones (opcional)',
    ]) !!}
</div>
