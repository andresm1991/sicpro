@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        {!! Form::model($cronograma, [
            'route' => [
                'proyecto.cronograma.update.actividad.dias.semana',
                [
                    'proyecto' => $proyecto->id,
                    'semana' => $semana,
                    'rubro' => $rubro,
                    'cronograma' => $cronograma->id,
                ],
            ],
            'class' => 'form-horizontal',
            'autocomplete' => 'off',
            'enctype' => 'multipart/form-data',
            'method' => 'PUT',
        ]) !!}
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="row d-flex justify-content-between">
                        <div class="col-md-8">
                            <h4 class="mt-2 font-weight-bold">Días semana</h4>

                        </div>

                        <div class="col-md-2">
                            <div class="select_wrapper">
                                <label class="rounded  text-white">
                                    <input type="checkbox" name="completadas" class="d-none" value="true"
                                        {{ $cronograma->completado ? 'checked' : '' }}
                                        {{ $cronograma->completado ? 'disabled' : '' }}>
                                    <span class="text-center d-block py-3">
                                        Actividades Completadas
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-2 ">
                            <button class="btn btn-dark btn-options btn-block">Guardar</button>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="card-body ">
                @include('cronograma.partials.form')
            </div>
        </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/cronograma_scripts.js') }}" type="module"></script>
@endsection
