@extends('layouts.app')

@section('title', 'Proformas')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                <a href="{{ route('proformas.tipo', 'adecentamientos') }}" class="nodo">
                    <img src="{{ asset('images/svg/bosquejo.svg') }}">
                    <span style="margin-right: 10px;margin-left: 10px;">Adecentamientos</span>
                </a>

                <a href="{{ route('proformas.tipo', 'diseno_planos') }}" class="nodo">
                    <img src="{{ asset('images/svg/bosquejo.svg') }}">
                    <span style="margin-right: 10px;margin-left: 10px;">Diseño de Planos</span>
                </a>
            </div>
        </div>
    </section>

@endsection
