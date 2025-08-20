@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')

    <section class="content" style="padding-bottom: 20px;">
        <div class="container-fluid">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex ">
                            <div class="col-md-10">
                                <h4 class="mt-2 font-weight-bold">Detalle de la Eventualidad</h4>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4 col-12">
                            <div class="form-group">
                                <label class="col-form-label">Colaborador <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span class="form-control">
                                    @if ($solicitud->usuariosEventualidad->count())
                                        {{ $solicitud->usuariosEventualidad->pluck('usuario.nombre')->join(', ') }}
                                    @else
                                        <span class="text-muted">Sin colaboradores</span>
                                    @endif
                                </span>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label">Estado Solicitud <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span class="form-control">{{ $solicitud->estado_solicitud->descripcion }}</span>
                            </div>

                        </div>
                        <div class="col-sm-8 col-12">
                            <div class="form-group">
                                <label class="col-form-label">Detalle <i class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <textarea class="form-control" rows="3" disabled>{{ $solicitud->detalle }}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="col-12 mt-4 p-0">
                        <p class="font-italic">La solicitud esta aprobada por lo tanto por seguridad no pude ser editada.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
