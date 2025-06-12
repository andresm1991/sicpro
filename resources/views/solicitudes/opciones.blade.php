@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container d-flex align-items-center justify-content-center full-height">
            <div class="row">
                <div class="max-auto">
                    <a href="{{ route('solicitud.permisos.index') }}" class="nodo">
                        <img src="{{ asset('images/svg/evaluacion.svg') }}" alt="">
                        <span>Ausencias</span>
                    </a>
                </div>
                <div class="max-auto">
                    <a href="{{ route('solicitud.eventualidad.index') }}" class="nodo">
                        <img src="{{ asset('images/svg/evaluacion.svg') }}" alt="">
                        <span>Eventualidades</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('solicitud.reposicion.index') }}" class="nodo">
                        <img src="{{ asset('images/svg/tiempo-de-trabajo.svg') }}" alt="">
                        <span>Reposición de Tiempo</span>
                    </a>
                </div>
                {{--  
                @if (auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Gerencial') || auth()->user()->hasRole('Administrativo'))
                    <div class="max-auto">
                        <a href="{{ route('solicitud.autorizacion.index') }}" class="nodo">
                            <img src="{{ asset('images/svg/administrador.svg') }}" alt="">
                            <span>Peticiones de Edición</span>
                        </a>
                    </div>
                @endif
                --}}
            </div>
        </div>
    </section>
@endsection
