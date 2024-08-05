<div class="form-group row">
    <div class="col-md-2">
        <label class="col-form-label">Nombres <i class="fa-regular fa-asterisk fa-2xs"></i></label>
    </div>

    <div class="col-md-10">
        {{ Form::text('nombres', old('nombres', $user->nombre), ['class' => 'form-control text-capitalize', 'placeholder' => 'Ingrese nombres']) }}
        {!! $errors->first('nombres', '<span class="help-block text-quicksand text-danger">:message</span>') !!}

    </div>
</div>

<div class="form-group row">
    <div class="col-md-2">
        <label class="col-form-label">Usuario <i class="fa-regular fa-asterisk fa-2xs"></i></label>
    </div>

    <div class="col-md-10">
        {{ Form::text('usuario', old('usuario', $user->usuario), ['class' => 'form-control not_blank_space', 'placeholder' => 'Ingrese nombre del usuario', 'id' => 'username']) }}
        {!! $errors->first('usuario', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>

</div>

<div class="row">
    <div class="col-md-2">
        <label class="col-form-label">Contraseña <i class="fa-regular fa-asterisk fa-2xs"></i></label>
    </div>

    <div class="col-md-5 form-group">
        {{ Form::password('password', ['class' => 'form-control', 'placeholder' => $user->id ? 'Actualizar contraseña' : 'Ingresar contraseña']) }}
        {!! $errors->first('password', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>

    <div class="col-md-5 form-group">
        {{ Form::password('confirm_password', ['class' => 'form-control', 'placeholder' => 'Confirmar contraseña']) }}
        {!! $errors->first('confirm_password', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>

</div>

<div class="row">
    <div class=" col-md-2">
        <label class="col-form-label">Contacto</label>
    </div>

    <div class="form-group col-md-3">
        {{ Form::text('telefono', old('telefono'), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono', 'maxlength' => '10']) }}
        {!! $errors->first('telefono', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="form-group col-md-7">
        {{ Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => 'Ingrese correo electrónico']) }}
        {!! $errors->first('email', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>

<div class="row">
    <div class=" col-md-2">
        <label class="col-form-label">Perfil <i class="fa-regular fa-asterisk fa-2xs"></i></label>
    </div>

    <div class="form-group col-md-6">
        {{ Form::select('perfil', $roles, old('perfil', $user->roles), ['class' => 'selectpicker show-tick show-menu-arrow form-control  border ', 'data-style' => '', 'title' => 'Seleccione perfil de usuario']) }}
        {!! $errors->first('perfil', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
    <div class="form-group col-md-4">
        {{ Form::select('activo', ['1' => 'Activo', '0' => 'Inactivo'], old('activo', $user->activo), ['class' => 'selectpicker show-tick show-menu-arrow form-control border ', 'data-style' => '', 'title' => 'Seleccione estado']) }}
        {!! $errors->first('activo', '<span class="help-block text-quicksand text-danger">:message</span>') !!}
    </div>
</div>
