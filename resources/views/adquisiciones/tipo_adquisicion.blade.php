@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container">
            <div class="row">
                @foreach ($aquisiciones as $item)
                    @if ($item->slug != 'profecionales')
                        <div class="col-md-6 d-flex justify-content-center align-items-center">
                            @switch($item->slug)
                                @case('contratista')
                                    {{--   <a href="{{ route('proyecto.adquisiciones.contratista', [
                                        'tipo' => $tipo,
                                        'tipo_id' => $tipo_id,
                                        'proyecto' => $proyecto->id,
                                        'tipo_adquisicion' => $tipo_adquisicion->id,
                                        'tipo_etapa' => $item->id,
                                    ]) }}"
                                        class="nodo">
                                        <img src="{{ asset(json_decode($item->detalle)->icono) }}" alt="">
                                        <span>{{ $item->descripcion }}</span>
                                    </a>
                                    --}}

                                    <a href="javascripts:void(0);" class="nodo">
                                        <img src="{{ asset(json_decode($item->detalle)->icono) }}" alt="">
                                        <span>{{ $item->descripcion }}</span>
                                    </a>
                                @break

                                @case('mano.obra')
                                    <a href="{{ route('proyecto.adquisiciones.mano.obra', [
                                        'tipo' => $tipo,
                                        'tipo_id' => $tipo_id,
                                        'proyecto' => $proyecto->id,
                                        'tipo_adquisicion' => $tipo_adquisicion->id,
                                        'tipo_etapa' => $item->id,
                                    ]) }}"
                                        class="nodo">
                                        <img src="{{ asset(json_decode($item->detalle)->icono) }}" alt="">
                                        <span>{{ $item->descripcion }}</span>
                                    </a>
                                @break

                                @default
                                    <a href="{{ route('proyecto.adquisiciones.tipo.etapa', [
                                        'tipo' => $tipo,
                                        'tipo_id' => $tipo_id,
                                        'proyecto' => $proyecto->id,
                                        'tipo_adquisicion' => $tipo_adquisicion->id,
                                        'tipo_etapa' => $item->id,
                                    ]) }}"
                                        class="nodo">
                                        <img src="{{ asset(json_decode($item->detalle)->icono) }}" alt="">
                                        <span>{{ $item->descripcion }}</span>
                                    </a>
                            @endswitch

                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endsection
