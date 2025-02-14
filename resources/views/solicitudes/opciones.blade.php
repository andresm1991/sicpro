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
                        <span>Solicitud de Permisos</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('solicitud.reposicion.index') }}" class="nodo">
                        <img src="{{ asset('images/svg/tiempo-de-trabajo.svg') }}" alt="">
                        <span>Reposición de Permisos</span>
                    </a>
                </div>


            </div>
        </div>
    </section>
@endsection
