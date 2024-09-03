@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container">
            <div class="row">
                @foreach ($menu_adquisiciones as $item)
                    <div class="col-md-6 d-flex justify-content-center align-items-center">
                        <a href="{{ route('proyecto.adquisiciones.tipo', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $item->id]) }}"
                            class="nodo">
                            <img src="{{ asset(json_decode($item->detalle)->icono) }}" alt="">
                            <span>{{ $item->descripcion }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
