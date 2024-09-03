@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container">
            <div class="row justify-content-center align-items-center full-height">
                @forelse ($tipo_proyectos as $tipo)
                    <a href="{{ route('proyecto.list', ['tipo' => str_replace(' ', '-', $tipo->descripcion), 'tipo_id' => $tipo->id]) }}"
                        class="nodo">
                        <img src="{{ asset(json_decode($tipo->detalle)->icono) }}">
                        <span style="margin-right: 10px;margin-left: 10px;">{{ $tipo->descripcion }}</span>
                    </a>
                @empty
                @endforelse

            </div>
        </div>
    </section>
@endsection
