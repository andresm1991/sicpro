@extends('layouts.app')

@section('title', $title_page)

@section('styles')
<style>
    /* Lightbox: hover en botones */
    #lightbox-prev:hover,
    #lightbox-next:hover {
        opacity: 1 !important;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
    }
    #lightbox-modal .close:hover {
        opacity: 1 !important;
    }
    /* Botón de lupa sobre imágenes Dropify */
    .btn-ver-imagen {
        opacity: 0.85;
        transition: opacity 0.15s ease;
    }
    .btn-ver-imagen:hover {
        opacity: 1;
    }
</style>
@endsection

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            {!! Form::model($propiedad, [
                'route' => ['gerencia.propiedades.update', $propiedad->id],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_propiedades',
                'method' => 'PUT',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <h4 class="col ">Información general</h4>
                            <div class="col text-right">
                                <a href='javascript:void(0);' class='btn btn-secondary btn-options compartir-ubicacion'
                                    data-ubicacion='{{ json_encode(['lat' => $propiedad->latitud, 'lng' => $propiedad->longitud]) }}'
                                    data-nombre='{{ $propiedad->nombre }}'>Compartir ubicacón</a>
                                <button class="btn btn-dark btn-options" form="form_propiedades">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    @include('gerencia.propiedades.partials.form')
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </section>

@endsection
