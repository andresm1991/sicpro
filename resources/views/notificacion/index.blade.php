@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Notificaciones</h5>
                        <hr>
                        <div class="list-group">
                            @forelse ($notificaciones as $notificacion)
                                <a href="javascript:void(0);"
                                    class="list-group-item list-group-item-action leer-notificacion {{ !$notificacion->leido ? 'list-group-item-dark' : '' }} "
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
                            @include('partials.pagination', [
                                'paginator' => $notificaciones,
                                'interval' => 5,
                            ])
                        </div>
                    </div>
                </div>
            </div> <!--.col-12-->

        </div><!--.row-->

    </section>


@endsection
