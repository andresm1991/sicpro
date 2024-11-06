@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fuild">
            {!! Form::model($adquisicion, [
                'route' => [
                    'administrativo.adquisicion.update', ['tipo' => $tipo,'adquisicion' => $adquisicion->id]
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
                                <h4>Orden de Recepcion #{{ numeroOrden($adquisicion->orden_recepcion, false) }}</h4>
                            </div>
                            
                            <div class="col-md-2 ">
                                <div class="select_wrapper">
                                    <label class="rounded  text-white">
                                        <input type="checkbox" name="orden_completa" class="d-none" value="true"
                                            {{ $adquisicion->orden_recepcion->completado == true ? 'checked' : '' }}
                                            disabled>
                                        <span class="text-center d-block py-3">Pedido Completo</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block">Guardar</button>
                            </div>
                        </div>
                        
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')

                    @include('administrativo.adquisiciones.partials.items')
                    
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </section>
@endsection
