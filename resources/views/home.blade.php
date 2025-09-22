@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    @include('partials.header_page')
    <section style="padding: 10px 0 50px 0;">
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                <div class="col-12 align-items-center d-flex justify-content-center">
                    <a href="{{ route('gerencia.index') }}" class="nodo" id="gerencia">
                        <img src="{{ asset('images/svg/manager.svg') }}" alt="Gerencia">
                        <span>Gerencia</span>
                    </a>
                </div>

                <div class="col-sm-3 col-12">
                    <a href="{{ route('proyecto.index') }}" class="nodo" id="operativo">
                        <img src="{{ asset('images/svg/constructor.svg') }}" alt="Operativo">
                        <span>Operativo</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('solicitud.index') }}" class="nodo" id="operativo">
                        <img src="{{ asset('images/svg/solicitud.svg') }}" alt="Operativo">
                        <span>Comunicación</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('marketing.index') }}" class="nodo" id="marketing">
                        <img src="{{ asset('images/svg/marketing.svg') }}" alt="Marketing">
                        <span>Marketing</span>
                    </a>
                </div>

                <div class="col-sm-3 col-12">
                    <a href="{{ route('administrativo.index') }}" class="nodo" id="administrativo">
                        <img src="{{ asset('images/svg/users.svg') }}" alt="Administrativo">
                        <span>Administrativo</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('tarea.index') }}" class="nodo" id="administrativo">
                        <img src="{{ asset('images/svg/lista-de-tareas.svg') }}" alt="Administrativo">
                        <span>Agenda</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('proformas.index') }}" class="nodo" id="administrativo">
                        <img src="{{ asset('images/svg/cotizacion.svg') }}" alt="Administrativo">
                        <span>Proformas</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('reporte.index') }}" class="nodo">
                        <img src="{{ asset('images/svg/analitica.svg') }}" alt="">
                        <span>Rerportes</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('sistema.index') }}" class="nodo" id="base-datos">
                        <img src="{{ asset('images/svg/sistema.svg') }}" alt="Base de Datos">
                        <span>Sistema</span>
                    </a>
                </div>
                {{--  <div>
                    <a href="#" class="circle central" id="gerencia">
                        <img src="{{ asset('images/svg/manager.svg') }}" alt="Gerencia">
                        <span>Gerencia</span>
                    </a>
                    <a href="{{ route('proyecto.index') }}" class="circle top-left" id="operativo">
                        <img src="{{ asset('images/svg/constructor.svg') }}" alt="Operativo">
                        <span>Operativo</span>
                    </a>

                    <a href="{{ route('solicitud.index') }}" class="circle top-center" id="operativo">
                        <img src="{{ asset('images/svg/solicitud.svg') }}" alt="Operativo">
                        <span>Solicitudes</span>
                    </a>

                    <a href="#" class="circle top-right" id="marketing">
                        <img src="{{ asset('images/svg/marketing.svg') }}" alt="Marketing">
                        <span>Marketing</span>
                    </a>
                    <a href="{{ route('administrativo.index') }}" class="circle bottom-left" id="administrativo">
                        <img src="{{ asset('images/svg/users.svg') }}" alt="Administrativo">
                        <span>Administrativo</span>
                    </a>

                    <a href="{{ route('tarea.index') }}" class="circle bottom-center" id="administrativo">
                        <img src="{{ asset('images/svg/lista-de-tareas.svg') }}" alt="Administrativo">
                        <span>Agenda</span>
                    </a>

                    <a href="{{ route('sistema.index') }}" class="circle bottom-right" id="base-datos">
                        <img src="{{ asset('images/svg/sistema.svg') }}" alt="Base de Datos">
                        <span>Sistema</span>
                    </a>
                </div> --}}
            </div>
        </div>
    </section>
@endsection
