@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        {!! Form::open([
            'route' => [
                'proyecto.cronograma.store.actividad.dias.semana',
                [
                    'proyecto' => $proyecto->id,
                    'semana' => $semana,
                    'rubro' => $rubro,
                ],
            ],
            'class' => 'form-horizontal',
            'autocomplete' => 'off',
            'enctype' => 'multipart/form-data',
        ]) !!}
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="row d-flex justify-content-center align-items-center">
                        <div class="col-md-10">
                            <h4 class="mt-2 font-weight-bold">Días semana</h4>
                        </div>

                        <div class="col-md-2 ">
                            <button class="btn btn-dark btn-options btn-block">Guardar</button>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="card-body ">
                <div class="row">
                    @include('partials.alerts')
                    @php
                        // Array con los días de la semana
                        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                    @endphp

                    @foreach ($dias as $dia)
                        <div class="col-sm-2 day-item">
                            <label class="font-weight-bold">{{ ucfirst($dia) }}</label>
                            <div class="fields-container form-group" id="{{ $dia }}-fields">
                                <select name="{{ $dia }}" class="form-control select2-tag actividades"
                                    data-placeholder="Selecciona actividad">
                                    <option value=""></option>
                                    @foreach ($actividades as $index => $nombre)
                                        <option value="{{ $index }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-success btn-sm add-actividad col-12"
                                data-day="{{ $dia }}">
                                <i class="fa-solid fa-plus"></i> Agregar actividad
                            </button>
                            <div class="row">
                                <div class="col-12 mt-2 actividades-seleccionadas">
                                    <!-- Aquí se mostrarán las actividades seleccionadas -->
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/cronograma_scripts.js') }}" type="module"></script>
@endsection
