@extends('layouts.app')

@section('title', $title_page)

@section('content')

    @include('partials.header_page')

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Lista de Categorías y Rubros</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-sm-6">
                            <legend class="custom-legend"><span>Rubros Construcción</span></legend>
                            <fieldset class="custom-fieldset">
                                <ul class="tree">
                                    @foreach ($categorias as $categoria)
                                        <li>
                                            <details open>
                                                <summary>{{ $categoria->nombre }}</summary>
                                                <ul>
                                                    @if ($categoria->rubrosPresupuesto->isNotEmpty())
                                                        @foreach ($categoria->rubrosPresupuesto as $rubro)
                                                            <li class="rubro" data-categoria="{{ $categoria->id }}"
                                                                data-rubro="{{ $rubro->nombre }}">{{ $rubro->nombre }}</li>
                                                        @endforeach
                                                    @else
                                                        <li class="rubro">No hay rubros disponibles</li>
                                                    @endif
                                                </ul>
                                            </details>
                                        </li>
                                    @endforeach
                                </ul>
                            </fieldset>
                        </div>
                        <div class="col-sm-6">
                            <legend class="custom-legend"><span>Formulario</span></legend>
                            <fieldset class="custom-fieldset">
                                <form>
                                    <div class="form-group">
                                        <label for="input1">Categoría</label>
                                        <select name="categoria" id="" class="select2-basic-single"
                                            data-placeholder="Seleccione opción">
                                            <option value=""></option>
                                            @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="input1">Etapa Construcción</label>
                                        <select name="etapa_construccion" id="" class="select2-basic-single"
                                            data-placeholder="Seleccione opción">
                                            <option value=""></option>
                                            @foreach ($etapas_construccion as $etapa)
                                                <option value="{{ $etapa->id }}">{{ $etapa->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="input1">Descripción Rubro</label>
                                        <input type="text" class="form-control" id="input1">
                                    </div>

                                    <button type="submit" class="btn btn-dark">Guardar</button>
                                </form>
                            </fieldset>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')

@endsection
