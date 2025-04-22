@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section>
        <div class="container d-flex align-items-center justify-content-center full-height">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card image-card" style="width: 18rem;">
                        <a href="{{ route('administrativo.menu.construccion') }}">
                            <img class="card-img-top uniform-image" src="{{ asset('images/logo_empresa.jpg') }}"
                                alt="Card image cap">
                        </a>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="card image-card" style="width: 18rem;">
                        <a href="{{ route('jp.limpieza.index') }}">
                            <img class="card-img-top uniform-image" src="{{ asset('images/logo_limpieza.jpeg') }}"
                                alt="Card image cap">
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
