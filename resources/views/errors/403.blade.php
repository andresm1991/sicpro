@extends('layouts.app')

@section('title', 'Acceso Restringido')

@section('content')
    @include('partials.header_page', ['title_page' => 'Acceso Restringido'])
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fa fa-lock fa-4x text-warning"></i>
                            </div>
                            <h1 class="display-4 text-muted">403</h1>
                            <h3 class="mb-3">Acceso Restringido</h3>
                            <p class="text-muted mb-4">
                                No tenés permisos para acceder a esta sección.<br>
                                Si creés que deberías tener acceso, comunicate con el administrador del sistema.
                            </p>
                            <a href="javascript:history.back()" class="btn btn-dark">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
