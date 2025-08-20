@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <div class="col-md-10">
                                <h5>Fecha: {{ dateFormatHumans($fecha_actual) }}</h5>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_mano_obra">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::open([
                        'route' => [
                            'proyecto.adquisiciones.mano.obra.store',
                            [
                                'tipo' => $tipo,
                                'tipo_id' => $tipo_id,
                                'tipo_adquisicion' => $tipo_adquisicion->id,
                                'tipo_etapa' => $tipo_etapa->id,
                                'proyecto' => $proyecto->id,
                                'mano_obra' => $mano_obra->id,
                            ],
                        ],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_mano_obra',
                    ]) !!}
                    {{ Form::hidden('proyecto_id', $proyecto->id) }}
                    {{ Form::hidden('tipo_adquisicion', $tipo_adquisicion->id) }}
                    {{ Form::hidden('tipo_etapa', $tipo_etapa->id) }}
                    {{ Form::hidden('fecha', $fecha_actual) }}

                    @include('mano_obra.partials.form')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
