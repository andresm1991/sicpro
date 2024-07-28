@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="d-flex justify-content-center align-items-center full-height">
            <div class="row w-100 mx-0 login-container">
                <div class="card col-lg-4 mx-auto">
                    <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                        <div class="img-fluid">
                            <img class="img-fluid" src="images/logo_250px.png" alt="logo">
                        </div>
                        <h4>¡Hola! empecemos</h4>
                        <h6 class="font-weight-light">Inicia sesión para continuar.</h6>
                        {!! Form::open(['route' => 'login', 'class' => 'pt-3']) !!}
                        <form class="pt-3">
                            <div class="form-group">
                                <input type="text" name="usuario" value="{{ old('usuario') }}"
                                    class="form-control form-control-lg @error('usuario') is-invalid @enderror"
                                    placeholder="Usuario" autofocus>

                                @error('usuario')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" name="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    placeholder="Contraseña">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-block btn-dark btn-lg font-weight-medium auth-form-btn">INICIAR
                                    SESIÓN</button>
                            </div>
                            <div class="my-2 d-flex justify-content-between align-items-center">

                                <a href="#" class="auth-link text-black">¿Has olvidado tu contraseña?</a>
                            </div>
                            {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
    </div>
@endsection
