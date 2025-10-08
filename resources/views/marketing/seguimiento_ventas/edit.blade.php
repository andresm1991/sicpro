@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    {{ Form::hidden('proceso_venta_id', $seguimientoVenta->id) }}
                    @include('partials.alerts')
                    @include('marketing.seguimiento_ventas.partials.form')

                </div>
            </div>
        </div>
    </section>

@endsection
