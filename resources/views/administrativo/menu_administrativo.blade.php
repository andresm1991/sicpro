@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container d-flex align-items-center justify-content-center full-height">
            <div class="row">
                <div class="max-auto">
                    <a href="{{ route('administrativo.adquisiciones') }}"
                        class="nodo">
                        <img src="{{ asset('images/icons/adquisicion.png') }}" alt="">
                        <span>Adquisiciones</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
