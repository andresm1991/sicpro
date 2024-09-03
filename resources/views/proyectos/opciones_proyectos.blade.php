@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                <div>
                    <a href="{{ route('proyecto.informacion.general', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]) }}"
                        class="circle central">
                        <img src="{{ asset('images/icons/informacion.png') }}" alt="">
                        <span>Información</span>
                        <span>General</span>
                    </a>
                    <a href="{{ route('proyecto.adquisiciones.menu', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]) }}"
                        class="circle top-left">
                        <img src="{{ asset('images/icons/adquisicion.png') }}" alt="">
                        <span>Adquisiciones</span>
                    </a>
                    <a href="#" class="circle top-right">
                        <img src="{{ asset('images/icons/presupuesto.png') }}" alt="">
                        <span>Presupuesto</span>
                    </a>
                    <a href="#" class="circle bottom-left">
                        <img src="{{ asset('images/icons/estado-de-resultados.png') }}" alt="">
                        <span>Cronograma</span>
                        <span>Valorado</span>
                    </a>
                    <a href="#" class="circle bottom-right">
                        <img src="{{ asset('images/icons/descripcion-general.png') }}" alt="">
                        <span>Rerportes</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    {{-- 
    <section class="content">
        <div class="container">
            <div class="row">
                <div class="circle-container">
                    <a href="" class="nodo" id="nodo-1">
                        <img src="{{ asset('images/icons/informacion.png') }}">
                        <span>Información</span>
                        <span>General</span>
                    </a>
                    <div class="circle">
                        <img src="{{ asset('images/icons/descripcion-general.png') }}" alt="Gerencia">
                        <span>Adquisiciones</span>
                    </div>
                    <div class="circle">
                        <img src="{{ asset('images/icons/presupuesto.png') }}" alt="Gerencia">
                        <span>Presupuesto</span>
                    </div>
                    <div class="circle">
                        <img src="{{ asset('images/icons/estado-de-resultados.png') }}" alt="Gerencia">
                        <span>Cronograma</span>
                        <span>Valorado</span>
                    </div>
                    <div class="circle">
                        <img src="{{ asset('images/icons/descripcion-general.png') }}" alt="Gerencia">
                        <span>Rerportes</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
     --}}
@endsection
