@extends('layouts.app')

@section('title', 'Materiales y Herramientas')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::model($proveedor, [
                        'route' => ['sistema.proveedor.update', ['menu_id' => Crypt::encrypt($menu_id), 'proveedor' => $proveedor->id]],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_proveedor',
                        'method' => 'PUT',
                    ]) !!}

                    @include('proveedores.partials.form')

                    <div class="col-12 text-center">
                        <button class="btn btn-dark btn-options">Guardar</button>
                    </div>


                    {!! Form::close() !!}

                    <div class="col-12 mt-4">
                        <p class="font-italic">Los campos con (*) son obligatorios, por favor complétalos corretéame</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
