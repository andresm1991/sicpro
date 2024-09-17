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
                            <a href="{{ route(
                                $item->slug == 'contratista'
                                    ? 'proyecto.adquisiciones.contratista'
                                    : ($item->slug == 'mano.obra'
                                        ? 'proyecto.adquisiciones.mano.obra'
                                        : 'proyecto.adquisiciones.tipo.etapa'),
                                [
                                    'tipo' => $tipo,
                                    'tipo_id' => $tipo_id,
                                    'proyecto' => $proyecto->id,
                                    'tipo_adquisicion' => $tipo_adquisicion->id,
                                    'tipo_etapa' => $item->id,
                                ],
                            ) }}"
                                class="nodo">
                                <img src="{{ asset(json_decode($item->detalle)->icono) }}" alt="">
                                <span>{{ $item->descripcion }}</span>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endsection
