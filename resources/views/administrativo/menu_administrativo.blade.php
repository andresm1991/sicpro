@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container d-flex align-items-center justify-content-center full-height">
            <div class="row">
                <div class="max-auto">
                    <a href="{{ route('administrativo.adquisiciones', 'operativo') }}"
                        class="nodo">
                        <img src="{{ asset('images/icons/adquisicion.png') }}" alt="">
                        <span>Adquisiciones Operativas</span>
                    </a>
                </div>

                <div class="max-auto">
                    <a href="{{ route('administrativo.adquisiciones', 'administrativo') }}"
                        class="nodo">
                        <img src="{{ asset('images/icons/adquisicion.png') }}" alt="">
                        <span>Adquisiciones Administrativas</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
