@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <h4 class="col ">Información general</h4>
                            <div class="col text-right">
                                <button class="btn btn-dark btn-options" form="form_proyectos">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::model($proyecto, [
                        'route' => ['proyecto.update', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_proyectos',
                        'method' => 'PUT',
                    ]) !!}

                    @include('proyectos.partials.form_informacion_general')

                    {!! Form::close() !!}

                    <div class="col-12 mt-4">
                        <p class="font-italic">Los campos con (*) son obligatorios, por favor complétalos
                            corretéame</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
