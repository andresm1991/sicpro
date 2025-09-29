@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::open([
                        'route' => ['marketing.seguimiento.ventas.store'],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_seguimiento_venta',
                    ]) !!}

                    @include('marketing.seguimiento_ventas.partials.form')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>

@endsection
