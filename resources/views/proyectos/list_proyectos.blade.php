@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-sm-12 text-center">
                    <a href="{{ route('proyecto.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id]) }}"
                        class="btn btn-dark btn-h50">Agregar
                        Proyecto </a>
                </div>
                @forelse ($proyectos as $proyecto)
                    <div class="content-card">
                        <div class="card neumorphism ">
                            <img src="{{ showImage($proyecto->portada) }}" class="card-img-top" alt="portada">
                            <div class="card-body">
                                <div class="card-title">
                                    <h5 class="text-truncate text-capitalize">{{ $proyecto->nombre_proyecto }}</h5>
                                    <small class="badge badge-warning">Nuevo</small>
                                </div>
                                <h6 class="card-text text-truncate-max-line-3 no-margin-bottom">
                                    Tipo proyecto:
                                    <span class="font-weight-normal">{{ $proyecto->tipo_proyecto->descripcion }}</span>
                                </h6>
                                <h6 class="card-text text-truncate-max-line-3 no-margin-bottom">Dirección:
                                    <span class="font-weight-normal">{{ $proyecto->direccion }}</span>
                                </h6>
                                <h6 class="card-text text-truncate-max-line-3 ">Fecha Finalización:
                                    <span class="font-weight-normal">{{ $proyecto->fecha_finalizacion }}</span>
                                </h6>
                                <div class="col-12 text-center">
                                    <a href="{{ route('proyecto.view', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto]) }}"
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
