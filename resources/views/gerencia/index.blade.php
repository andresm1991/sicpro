@extends('layouts.app')

@section('title', 'JP Limpieza y Mantenimiento')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                <a href="{{ route('gerencia.propiedades.index') }}" class="nodo">
                    <img src="{{ asset('images/svg/terrenos-en-venta.svg') }}">
                    <span style="margin-right: 10px;margin-left: 10px;">Propiedades en venta</span>
                </a>
            </div>
        </div>
    </section>

@endsection
