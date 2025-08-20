@extends('layouts.app')

@section('title', 'Opciones del Proyecto')

@section('content')
    @include('partials.header_page')
    <section class="content">
        <div class="container">
            <div class="row d-flex justify-content-center align-items-center full-height">
                <div class="col-sm-3 col-12">
                    <a href="{{ route('jp.limpieza.proyectos.edit', $proyecto->id) }}" class="nodo">
                        <img src="{{ asset('images/icons/informacion.png') }}" alt="">
                        <span>Información</span>
                        <span>General</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('jp.limpieza.adquisiciones.index', $proyecto->id) }}" class="nodo center">
                        <img src="{{ asset('images/icons/adquisicion.png') }}" alt="">
                        <span>Adquisiciones</span>
                    </a>
                </div>
                <div class="col-sm-3 col-12">
                    <a href="{{ route('jp.limpieza.presupuesto.index', $proyecto->id) }}" class="nodo">
                        <img src="{{ asset('images/icons/presupuesto.png') }}" alt="">
                        <span>Presupuesto</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
