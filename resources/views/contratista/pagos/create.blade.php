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
                                <h5>Orden de trabajo Nro. {{ numeroOrden($orden_trabajo, false) }}</h5>
                                <span>Fecha. {{ dateFormatHumans(date('Y-m-d')) }}</span>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_pago_orden_trabajo">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::open([
                        'route' => [
                            'proyecto.adquisiciones.contratista.guardar.pago.orden.trabajo',
                            [
                                'tipo' => $tipo,
                                'tipo_id' => $tipo_id,
                                'tipo_adquisicion' => $tipo_adquisicion->id,
                                'tipo_etapa' => $tipo_etapa->id,
                                'proyecto' => $proyecto->id,
                                'contratista' => $orden_trabajo->id,
                            ],
                        ],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_pago_orden_trabajo',
                    ]) !!}
                    {{ Form::hidden('proyecto_id', $proyecto->id) }}
                    {{ Form::hidden('tipo_adquisicion', $tipo_adquisicion->id) }}
                    {{ Form::hidden('tipo_etapa', $tipo_etapa->id) }}
                    {{ Form::hidden('orden_trabajo', $orden_trabajo->id) }}

                    @include('contratista.pagos.partial.form')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
