@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    @include('partials.header_page')

    <section class="content">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::model($user, [
                        'route' => ['perfil.update', $user->id],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'method' => 'PUT',
                    ]) !!}
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        {!! Form::text('name', $user->nombre, ['class' => 'form-control', 'id' => 'name', 'disabled']) !!}
                    </div>
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        {!! Form::text('username', $user->usuario, ['class' => 'form-control', 'id' => 'usuario', 'disabled']) !!}
                    </div>

                    <div class="form-group">
                        <label for="lbl-password">Contraseña</label>
                        {!! Form::password('password', [
                            'class' => 'form-control',
                            'id' => 'lbl-password',
                            'placeholder' => 'Actualizar contraseña',
                        ]) !!}

                        {!! $errors->first('password', '<span class="help-block text-danger">:message</span>') !!}
                    </div>
                    <div class="form-group">
                        <label for="lbl-config-password">Contraseña</label>
                        {!! Form::password('password_confirmation', [
                            'class' => 'form-control',
                            'id' => 'lbl-config-password',
                            'placeholder' => 'Confirmar contraseña',
                        ]) !!}
                        {!! $errors->first('password_confirmation', '<span class="help-block text-danger">:message</span>') !!}
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        {!! Form::text('correo', $user->correo, [
                            'class' => 'form-control',
                            'id' => 'correo',
                            'placeholder' => 'Ingresar dirección de correo',
                        ]) !!}
                        {!! $errors->first('correo', '<span class="help-block text-danger">:message</span>') !!}
                    </div>

                    <div class="form-group">
                        <label for="">Teléfono de contacto</label>
                        {!! Form::text('telefono', $user->telefono, [
                            'class' => 'form-control solo-numeros',
                            'maxlength' => '10',
                            'placeholder' => 'Ingresar teléfono de contacto',
                        ]) !!}
                        {!! $errors->first('telefono', '<span class="help-block text-danger">:message</span>') !!}
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-block btn-dark btn-lg font-weight-medium auth-form-btn">GUARDAR</button>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
