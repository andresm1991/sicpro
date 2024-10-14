@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content">
        <div class="container">
            <div class="row justify-content-center">
                <a href="{{ route('sistema.proveedor.menu') }}" class="nodo">
                    <img src="{{ asset('images/icons/proveedor.png') }}">
                    <span>Proveedores</span>
                </a>

                <a href="{{ route('sistema.articulo.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/producto.png') }}">
                    <span>Productos</span>
                </a>

                <a href="{{ route('sistema.inventario.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/inventario.png') }}">
                    <span>Inventario</span>
                </a>

                <a href="{{ route('sistema.users.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/personas.png') }}">
                    <span>Usuarios</span>
                </a>

                {{-- <a href="{{ route('sistema.config.index') }}" class="nodo">
                    <img src="{{ asset('images/icons/configuracion.png') }}">
                    <span>Configuración</span>
                </a> --}}
            </div>

        </div>
    </section>
@endsection
