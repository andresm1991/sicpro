@extends('layouts.app')

@section('title', 'Proyectos')

@section('content')
    @include('partials.header_page')
    <section>
        @include('partials.alerts')
        <div class="container">
            <div class="row align-items-center">
                <div class="col-sm-12 text-center">
                    <a href="{{ route('jp.limpieza.proyectos.create') }}" class="btn btn-dark btn-h50">Agregar Proyecto </a>
                </div>
                @forelse ($proyectos as $proyecto)
                    <div class="content-card">
                        <div class="card neumorphism ">
                            <div class="image-container">
                                <div class="loader"></div>
                                <img src="{{ doTemporaryUrl($proyecto->archivo_portada) }}" class="card-img-top"
                                    alt="portada" onload="imageLoaded(this)">
                            </div>
                            <div class="card-body">
                                <div class="card-title">
                                    <h5 class="text-truncate text-capitalize">{{ $proyecto->nombre_proyecto }}</h5>
                                    <small class="badge badge-warning">{{ $proyecto->estado->descripcion }}</small>
                                </div>
                                <h6 class="card-text text-truncate-max-line-1 no-margin-bottom">
                                    Entidad:
                                    <span class="font-weight-normal">{{ $proyecto->entidad }}</span>
                                </h6>

                                <h6 class="card-text text-truncate-max-line-1 ">Fecha Finalización:
                                    <span class="font-weight-normal">{{ $proyecto->fecha_finalizacion }}</span>
                                </h6>
                                <div class="col-12 text-center">
                                    <a href="{{ route('jp.limpieza.proyectos.show', $proyecto->id) }}"
                                        class="btn btn-secondary">Más información</a>
                                </div>

                            </div>
                        </div>
                    </div>
                @empty
                    <div class=" mx-auto d-flex justify-content-center align-items-center full-height">
                        <div class="row text-center">
                            <div class="col-12">
                                <h4 class="text-white ">
                                    No existen proyectos registrados.
                                </h4>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
