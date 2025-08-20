@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fuild">
            {!! Form::model($orden_recepcion, [
                'route' => [
                    'proyecto.adquisiciones.orden.recepcion.update',
                    [
                        'tipo' => $tipo,
                        'tipo_id' => $tipo_id,
                        'tipo_adquisicion' => $tipo_adquisicion->id,
                        'tipo_etapa' => $tipo_etapa->id,
                        'proyecto' => $proyecto->id,
                        'pedido' => $pedido->id,
                        'orden_recepcion' => $orden_recepcion->id,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_order_recepcion',
                'method' => 'PUT',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>Orden de Pedido #{{ $pedido->numero }}</h4>
                            </div>
                            <div class="col-md-2 ">
                                <div class="select_wrapper">
                                    <label class="rounded  text-white">
                                        <input type="checkbox" name="orden_completa" class="d-none" value="true"
                                            {{ old('orden_completa') == true || (isset($orden_recepcion->completado) && $orden_recepcion->completado == true) ? 'checked' : '' }}
                                            {{ !$orden_recepcion->editar ? 'disabled' : '' }}>
                                        <span class="text-center d-block py-3">Pedido Completo</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 ">
                                @if (!$orden_recepcion->editar)
                                    <button type="button" class="btn btn-dark btn-options btn-block">Guardar</button>
                                @else
                                    <button class="btn btn-dark btn-options btn-block">Guardar</button>
                                @endif

                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {{ Form::hidden('pedido', $pedido->id) }}

                    @include('adquisiciones.orden_recepcion.partials.form')
                    @include('adquisiciones.orden_recepcion.partials.items')

                    @if ($orden_recepcion->completado)
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                            role="alert">
                            <i class="fa-regular fa-triangle-exclamation fa-3x"></i>
                            <small class="mx-4">La orden de recepción fue completada. Por motivos de seguridad, si desea
                                actualizar la información, por favor solicite al administrador que habilite esta orden. Para
                                hacerlo, haga clic en el siguiente enlace: <a href="#"
                                    class="text-dark font-weight-bold"> Solicitar
                                    edición de la orden.</a></small>
                        </div>
                    @endif
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </section>
@endsection
