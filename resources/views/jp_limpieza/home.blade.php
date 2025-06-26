@extends('layouts.app')

@section('title', 'JP Limpieza y Mantenimiento')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                <a href="{{ route('jp.limpieza.proyectos.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/cierre.png') }}">
                    <span style="margin-right: 10px;margin-left: 10px;">Proyectos</span>
                </a>

                <a href="{{ route('jp.limpieza.adquisiciones.administrativas.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/adquisicion.png') }}">
                    <span style="margin-right: 10px;margin-left: 10px;">Adquisiciones administrativas</span>
                </a>
                <a href="{{ route('jp.limpieza.mano.obra.administrativa.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/mano_obra.png') }}">
                    <span style="margin-right: 10px;margin-left: 10px;">mano de obra administrativa</span>
                </a>
                <a href="{{ route('jp.limpieza.caja.index') }}" class="nodo">
                    <img src="{{ asset('images/svg/flujo-de-efectivo.svg') }}" alt="">
                    <span>caja</span>
                </a>
                <a href="{{ route('jp.limpieza.reporte.index') }}" class="nodo">
                    <img src="{{ asset('images/svg/analitica.svg') }}" alt="">
                    <span>reportes</span>
                </a>
            </div>
        </div>
    </section>

@endsection
