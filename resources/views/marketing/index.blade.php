@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                <a href="{{ route('marketing.seguimiento.ventas.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/proveedor.png') }}">
                    <span>Seguimiento de ventas</span>
                </a>
            </div>

        </div>
    </section>
@endsection
