@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container-fluid">
            @include('partials.alerts')

            {!! Form::model($cliente, [
                'route' => ['sistema.clientes.update', $cliente->id],
                'class' => 'form-horizontal',
                'autocomplete' => 'off',
                'enctype' => 'multipart/form-data',
                'id' => 'form_clientes',
                'method' => 'PUT',
            ]) !!}
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-between align-items-center">
                            <div class="col-md-8">
                                <h4 class="font-weight-bolder">Información general</h4 </div>

                            </div>
                            <div class="col-md-2 ">
                                <button class="btn btn-dark btn-options btn-block" form="form_clientes">Guardar</button>
                            </div>
                    </li>
                </ul>
                <div class="card-body">
                    @include('clientes.partials.form')
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </section>

@endsection
