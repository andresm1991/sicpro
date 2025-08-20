@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content">
        <div class="container">
            <div class="row justify-content-center">
                @foreach ($categories as $category)
                    <a href="{{ route('sistema.config.detalle', $category->id) }}" class="nodo">
                        <span>{{ $category->descripcion }}</span>
                    </a>
                @endforeach
            </div>

        </div>
    </section>
@endsection
