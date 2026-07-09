@extends('layouts.app')

@section('title', 'Editar Pago Contratista')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            {!! Form::model($pago, [
                'route' => [
                    'jp.limpieza.contratistas.pagos.update',
                    [
                        'proyecto' => $proyecto,
                        'contratista' => $contratista,
                        'pago' => $pago->id,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_pagos_contratista',
                'method' => 'PUT',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>Orden de Pedido #{{ $pago->numero_formatted }}</h4>
                                <h6>Fecha: {!! Form::text('fecha', old('fecha', $pago->fecha_formatted), ['class' => 'auto-ajustable', 'readonly' => true]) !!} <i class="fa-regular fa-calendar-days datepicker-2"
                                        id="fecha"></i></h6>
                            </div>

                            <div class="col-md-2">
                                @can('jp_limpieza.contratistas.editar')
                                    <button class="btn btn-dark btn-options btn-block"
                                        form="form_pagos_contratista">Guardar</button>
                                @else
                                    <button class="btn btn-dark btn-options btn-block" form="form_pagos_contratista"
                                        {{ $pago->estado->slug == 'estados.pagos.prestamos.pagado' ? 'disabled' : '' }}>Guardar</button>
                                @endcan

                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')

                    @include('jp_limpieza.contratistas.pagos.partials.form')


                    @if ($pago->estado->slug == 'estados.pagos.prestamos.pagado')
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                            role="alert">
                            <i class="fa-regular fa-triangle-exclamation fa-3x"></i>
                            <small class="mx-4">La orden de recepción fue completada. Por motivos de seguridad, si desea
                                actualizar la información, por favor solicite al administrador que habilite esta orden. Para
                                hacerlo, haga clic en el siguiente enlace: <a href="javascript:void(0);"
                                    class="text-dark font-weight-bold -solicitar-edicion-pedido" id={{ $pago->id }}>
                                    Solicitar
                                    edición de la orden.</a>
                            </small>
                        </div>
                    @endif
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </section>

@endsection
