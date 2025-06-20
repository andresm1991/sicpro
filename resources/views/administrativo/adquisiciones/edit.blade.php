@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fuild">
            {!! Form::model($adquisicion, [
                'route' => [
                    $tipo == 'operativo' ? 'administrativo.adquisicion.update' : 'administrativo.recepcion.create',
                    ['tipo' => $tipo, 'adquisicion' => $adquisicion->id],
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
                                <h4>Orden de Pedido #{{ $adquisicion->numero }}</h4>
                            </div>

                            <div class="col-md-2 ">
                                <div class="select_wrapper">
                                    <label class="rounded  text-white">
                                        <input type="checkbox" name="orden_completa" class="d-none" value="true"
                                            {{ $adquisicion->estado == 'Completado' ? 'checked' : '' }}
                                            {{ $tipo == 'operativo' && $adquisicion->estado == 'Completado' ? 'readonly' : '' }}>
                                        <span
                                            class="text-center d-block py-3">{{ $adquisicion->tipo_etapa->descripcion == 'Servicios' ? 'Pedido Pagado' : 'Pedido Completo' }}
                                        </span>
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

                    @include('administrativo.adquisiciones.partials.form_recepcion')
                    @include('administrativo.adquisiciones.partials.items')

                </div>
            </div>
            {{ Form::close() }}
        </div>
    </section>

    @include('modals.agregar_productos_modal', ['adquisicion' => $adquisicion])
@endsection
@section('scripts')
    <script src="{{ asset('js/administrativo_scripts.js') }}" type="module"></script>
@endsection
