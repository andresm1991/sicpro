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
                                <h4 class="mt-2 font-weight-bold">Detalle de solicitud</h4>
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
                                <span class="form-control">{{ $solicitud->usuario->nombre }}</span>
                            </div>
                        </div>

                        <div class="col-sm-4 col-12">
                            <div class="form-group">
                                <label class="col-form-label">Tipo Solicitud <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span class="form-control">{{ $solicitud->tipo_solicitud->descripcion }}</span>
                            </div>
                        </div>

                        <div class="col-sm-4 col-12">
                            <div class="form-group">
                                <label class="col-form-label">Estado Solicitud <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span class="form-control">{{ $solicitud->estado_solicitud->descripcion }}</span>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Fecha Desde <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span class="form-control">{{ $solicitud->fecha_desde }}</span>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Fecha Hasta <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span class="form-control">{{ $solicitud->fecha_hasta }}</span>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Hora Desde <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span
                                    class="form-control">{{ \Carbon\Carbon::createFromFormat('H:i:s', $solicitud->hora_desde)->format('H:i') }}</span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Hora Hasta <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <span
                                    class="form-control">{{ \Carbon\Carbon::createFromFormat('H:i:s', $solicitud->hora_hasta)->format('H:i') }}</span>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="col-form-label">Tiempo total</label><br>
                                <span class="form-control">{{ $solicitud->tiempo_total }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-form-label">Detalle Solicitud <i
                                        class="fa-regular fa-asterisk fa-2xs"></i></label>
                                <textarea class="form-control" rows="3" readonly>{{ $solicitud->detalle }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" {{ $solicitud->recuperable ? 'checked' : '' }}
                            disabled>
                        <label>Recuperable</label>
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
