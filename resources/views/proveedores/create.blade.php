@extends('layouts.app')

@section('title', 'Materiales y Herramientas')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::open([
                        'route' => ['sistema.proveedor.store', Crypt::encrypt($menu_id)],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_proveedor',
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

@section('scripts')
    <script src="{{ asset('js/proveedor_scripts.js') }}"></script>
@endsection
