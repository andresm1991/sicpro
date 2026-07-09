@extends('layouts.app')

@section('title', 'Editar Contratista')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            {!! Form::model($contratista, [
                'route' => [
                    'jp.limpieza.contratistas.update',
                    [
                        'proyecto' => $proyecto->id,
                        'contratista' => $contratista->id,
                    ],
                ],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_contratista',
                'method' => 'PUT',
            ]) !!}

            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-8">
                                <h4>orden de trabajo #{!! Form::text('numero', $contratista->numero, ['class' => 'no-border', 'readonly' => true]) !!}</h4>
                                <h6>Fecha: {!! Form::text('fecha', old('fecha', $contratista->fecha_formateada), [
                                    'class' => 'auto-ajustable',
                                    'readonly' => true,
                                ]) !!} <i class="fa-regular fa-calendar-days datepicker-2"
                                        id="fecha"></i></h6>
                            </div>

                            <div class="col-md-2">
                                @can('jp_limpieza.contratistas.editar')
                                    <button class="btn btn-dark btn-options btn-block"
                                        form="form_contratista">Guardar</button>
                                @else
                                    <button class="btn btn-dark btn-options btn-block"
                                        form="form_contratista">Guardar</button>
                                @endcan

                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    @include('jp_limpieza.contratistas.partials.form')
                    @include('jp_limpieza.contratistas.partials.items')

                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </section>

@endsection
