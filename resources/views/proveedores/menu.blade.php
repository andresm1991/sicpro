@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
    @include('partials.header_page')
    <section class="content">
        <div class="container">
            <div class="row justify-content-center">
                @forelse ($menus as $menu)
                    <a href="{{ route('sistema.proveedor.index', Crypt::encrypt($menu->id)) }}" class="nodo">
                        <img src="{{ asset(json_decode($menu->detalle)->icono) }}">
                        <span>{{ $menu->descripcion }}</span>
                    </a>

                @empty
                @endforelse
            </div>

        </div>
    </section>
@endsection
