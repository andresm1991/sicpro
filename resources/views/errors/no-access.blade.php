@extends('layouts.app')

@section('title', 'Acceso restringido')

@section('content')
    <section>
        <div class="container">
            <div id="notfound">
                <div class="notfound">
                    <div class="notfound-404">
                        <h1>Oops!</h1>
                    </div>
                    <h2>403 - Acceso restringido</h2>
                    <p>No tiene acceso para ingresar en esta sección, por favor comuníquese con el administrador del sistema
                        para obtener ayuda.</p>
                    <a href="{{ route('home') }}">Ir a la página de inicio</a>
                </div>
            </div>
        </div>
    </section>
@endsection
