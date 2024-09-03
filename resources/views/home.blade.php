@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                <div>
                    <a href="#" class="circle central" id="gerencia">
                        <img src="{{ asset('images/icons/manager.svg') }}" alt="Gerencia">
                        <span>Gerencia</span>
                    </a>
                    <a href="{{ route('proyecto.index') }}" class="circle top-left" id="operativo">
                        <img src="{{ asset('images/icons/constructor.svg') }}" alt="Operativo">
                        <span>Operativo</span>
                    </a>
                    <a href="#" class="circle top-right" id="marketing">
                        <img src="{{ asset('images/icons/marketing.svg') }}" alt="Marketing">
                        <span>Marketing</span>
                    </a>
                    <a href="#" class="circle bottom-left" id="administrativo">
                        <img src="{{ asset('images/icons/users.svg') }}" alt="Administrativo">
                        <span>Administrativo</span>
                    </a>
                    <a href="{{ route('sistema.index') }}" class="circle bottom-right" id="base-datos">
                        <img src="{{ asset('images/icons/sistema.png') }}" alt="Base de Datos">
                        <span>Sistema</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
