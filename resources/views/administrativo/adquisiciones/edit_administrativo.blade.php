@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fuild">
            {!! Form::model($adquisicion, [
                'route' => ['administrativo.adquisicion.update', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id]],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_order_pedido',
                'method' => 'PUT',
            ]) !!}
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>Orden de Pedido #{{ $adquisicion->numero }}</h4>
                                <h6>Fecha: {!! Form::text('fecha', old('fecha', date('Y-m-d', strtotime($adquisicion->fecha))), [
                                    'class' => 'auto-ajustable',
                                    'readonly' => true,
                                ]) !!} <i class="fa-regular fa-calendar-days datepicker-2"
                                        id="fecha"></i></h6>
                            </div>

                            <div class="col-md-2 ">
                                <div class="select_wrapper">
                                    <label class="rounded  text-white">
                                        <input type="checkbox" name="orden_completa" class="d-none" value="true"
                                            {{ $adquisicion->estado == 'Completado' ? 'checked' : '' }}
                                            {{ $tipo == 'operativo' && $adquisicion->estado == 'Completado' ? 'disabled' : '' }}>
                                        <span
                                            class="text-center d-block py-3">{{ isset($adquisicion->tipo_etapa) && $adquisicion->tipo_etapa->descripcion == 'Servicios' ? 'Pedido Pagado' : 'Pedido Completo' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_order_pedido">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')

                    @include('administrativo.adquisiciones.partials.form')
                    @include('administrativo.adquisiciones.partials.items')
                    {{--  
                    @if (isset($adquisicion->orden_recepcion) && $adquisicion->orden_recepcion->completado)
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
                    --}}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </section>

@endsection
