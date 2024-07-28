@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px;">
        <div class="container">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h5 class="mt-2">Datos generales</h5>
                    </li>
                </ul>
                <div class="card-body">
                    @include('partials.alerts')
                    {!! Form::model($user, [
                        'route' => ['sistema.users.update', $user->id],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'method' => 'PUT',
                    ]) !!}

                    @include('user.partials.form')


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
