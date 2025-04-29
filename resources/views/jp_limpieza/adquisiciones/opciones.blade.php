@extends('layouts.app')

@section('title', 'Adquisiciones')

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container">
            <div class="row">
                @foreach ($menu_adquisiciones as $item)
                    <div class="col-md-6 d-flex justify-content-center align-items-center">
                        <a href="{{ route('jp.limpieza.adquisiciones.tipo.adquisicion', ['proyecto' => $proyecto, 'tipo_adquisicion' => $item->slug]) }}"
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
