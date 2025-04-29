@extends('layouts.app')

@section('title', 'Editar Adquisición')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            {!! Form::model($adquisicion, [
                'route' => [
                    'jp.limpieza.adquisiciones.update',
                    [
                        'proyecto' => $proyecto->id,
                        'adquisicion' => $adquisicion->id,
                        'tipo_adquisicion' => $tipoAdquisicion->slug,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_adquisicion',
                'method' => 'PUT',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>Orden de Pedido #{{ $adquisicion->numero }}</h4>
                                <h6>Fecha: {{ $adquisicion->fecha_formateada }}</h6>
                            </div>

                            @include('adquisiciones.partials.boton_pedido_completo')

                            <div class="col-md-2">
                                @if (auth()->user()->hasRole(['Administrador', 'Gerencial']))
                                    <button class="btn btn-dark btn-options btn-block"
                                        form="form_adquisicion">Guardar</button>
                                @else
                                    <button class="btn btn-dark btn-options btn-block" form="form_adquisicion"
                                        {{ $adquisicion->estado == 'completado' ? 'disabled' : '' }}>Guardar</button>
                                @endif

                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')


                    @include('jp_limpieza.adquisiciones.partials.form')
                    @include('jp_limpieza.adquisiciones.partials.items')


                    @if ($adquisicion->estado == 'completado')
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                            role="alert">
                            <i class="fa-regular fa-triangle-exclamation fa-3x"></i>
                            <small class="mx-4">La orden de recepción fue completada. Por motivos de seguridad, si desea
                                actualizar la información, por favor solicite al administrador que habilite esta orden. Para
                                hacerlo, haga clic en el siguiente enlace: <a href="javascript:void(0);"
                                    class="text-dark font-weight-bold -solicitar-edicion-pedido" id={{ $adquisicion->id }}>
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
@section('scripts')
    <script src="{{ asset('js/jp_limpieza/adquisiciones.js') }}"></script>
@endsection
