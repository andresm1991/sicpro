@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        {!! Form::model($proyecto, [
            'route' => [
                'proyecto.cronograma.update.actividades.semana',
                [
                    'proyecto' => $proyecto->id,
                    'semana' => $semana,
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
                            <h4 class="mt-2 font-weight-bold">Actividades</h4>

                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <a href="{{ route('pdf.export.cronograma.actividades.dias', [$proyecto->id, $semana]) }}"
                                    class="btn btn-secondary btn-options" target="_blank">
                                    <i class="fa-light fa-file-export"></i> Generar PDF
                                </a>
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
        {{ Form::close() }}
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/cronograma_scripts.js') }}" type="module"></script>
@endsection
