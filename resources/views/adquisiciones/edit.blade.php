@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            @include('partials.alerts')

            {!! Form::model($orden_pedido, [
                'route' => [
                    'proyecto.adquisiciones.update',
                    [
                        'tipo' => $tipo,
                        'tipo_id' => $tipo_id,
                        'tipo_adquisicion' => $tipo_adquisicion->id,
                        'tipo_etapa' => $tipo_etapa->id,
                        'proyecto' => $proyecto->id,
                        'pedido' => $orden_pedido->id,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_order_pedido',
                'method' => 'PUT',
            ]) !!}
            {{ Form::hidden('numero_pedido', $numero_orden) }}
            {{ Form::hidden('proyecto_id', $proyecto->id) }}
            {{ Form::hidden('tipo_adquisicion', $tipo_adquisicion->id) }}
            {{ Form::hidden('tipo_etapa', $tipo_etapa->id) }}
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>Orden de Pedido #{{ $numero_orden }}</h4>
                            </div>

                            @include('adquisiciones.partials.boton_pedido_completo')

                            <div class="col-md-2 ">
                                @if (auth()->user()->hasRole(['Administrador', 'Gerencial']))
                                    <button class="btn btn-dark btn-options btn-block"
                                        form="form_order_pedido">Guardar</button>
                                @else
                                    <button class="btn btn-dark btn-options btn-block" form="form_order_pedido"
                                        {{ isset($orden_pedido->orden_recepcion->completado) && $orden_pedido->orden_recepcion->completado ? 'disabled' : '' }}>Guardar</button>
                                @endif

                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('adquisiciones.partials.form')

                    @if (isset($orden_pedido->orden_recepcion->completado) && $orden_pedido->orden_recepcion->completado)
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                            role="alert">
                            <i class="fa-regular fa-triangle-exclamation fa-3x"></i>
                            <small class="mx-4">La orden de recepción fue completada. Por motivos de seguridad, si desea
                                actualizar la información, por favor solicite al administrador que habilite esta orden. Para
                                hacerlo, haga clic en el siguiente enlace: <a href="javascript:void(0);"
                                    class="text-dark font-weight-bold -solicitar-edicion-pedido"
                                    id={{ $orden_pedido->orden_recepcion->id }}>
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
