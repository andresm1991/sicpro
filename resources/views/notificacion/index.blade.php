@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Mis notificaciones</h4>
                    </li>
                </ul>
                <div class="card-body">
                    <div class="list-group">
                        @forelse ($notificaciones as $notificacion)
                            <a href="javascript:void(0);"
                                class="list-group-item list-group-item-action {{ !$notificacion->leido ? 'list-group-item-dark leer-notificacion' : '' }} "
                                data-id="{{ $notificacion->id }}" data-url="{{ $notificacion->message->url }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $notificacion->message->title }}</h5>
                                    <small>{{ $notificacion->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $notificacion->message->body }}</p>
                            </a>
                        @empty
                            <div class="alert alert-info" role="alert">
                                No tienes notificaciones pendientes.
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        @include('partials.pagination', ['paginator' => $notificaciones, 'interval' => 5])
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection
