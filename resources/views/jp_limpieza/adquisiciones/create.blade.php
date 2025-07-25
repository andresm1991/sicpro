@extends('layouts.app')

@section('title', 'nueva adquisicion')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            @include('partials.alerts')

            {!! Form::open([
                'route' => [
                    'jp.limpieza.adquisiciones.store',
                    [
                        'tipo_adquisicion' => isset($tipoAdquisicion->slug) ? $tipoAdquisicion->slug : 'null',
                        'proyecto' => $proyecto->id ?? 0,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_pedido',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>Adquisición #{!! Form::text('numero', $numero, ['class' => 'no-border', 'readonly' => true]) !!}</h4>
                            </div>
                            @include('adquisiciones.partials.boton_pedido_completo')
                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_pedido">Guardar</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('jp_limpieza.adquisiciones.partials.form')
                    @include('jp_limpieza.adquisiciones.partials.items')
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </section>

@endsection
@section('scripts')
    <script src="{{ asset('js/jp_limpieza/adquisiciones.js?v=' . config('app.version', '')) }}"></script>
@endsection
