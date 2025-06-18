@extends('layouts.app')

@section('title', 'Editar Planificación')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            @include('partials.alerts')
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="col-md-10">
                                <h5></h5>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_mano_obra">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">

                    {!! Form::model($planificacion, [
                        'route' => [
                            'jp.limpieza.mano.obra.update',
                            [
                                'proyecto' => isset($proyecto) ? $proyecto->id : 0,
                                'mano_obra' => $planificacion->id,
                            ],
                        ],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_mano_obra',
                        'method' => 'PUT',
                    ]) !!}

                    @include('jp_limpieza.mano_obra.partials.form')
                    @include('jp_limpieza.mano_obra.partials.tabla_personal')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
