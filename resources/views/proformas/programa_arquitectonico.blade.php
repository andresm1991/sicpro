@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')

    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            @include('partials.alerts')

            <div class="card">
                {{ Form::open(['route' => ['proformas.programa.arquitectonico.store', $tipo, $proforma->id, $programa->id]]) }}
                <div class="card-body ">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="row d-flex justify-content-between">
                                <div class="col-md-8">
                                    <div class="form-group">

                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-dark btn-options btn-block">Guardar</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('pdf.programa.arquitectonico', $programa->id) }}" target="_blank"
                                        class="btn btn-secondary btn-options btn-block">generar pdf</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-sm-4 form-group">
                            <label for="" class="col-form-label">Estilo</label>
                            <input type="text" name="estilo" class="form-control"
                                value="{{ $programa->estilo ?? 'CONTEMPORANEA' }}">
                        </div>
                        <div class="col-sm-8 form-group">
                            <label for="" class="col-form-label">Link Referencia</label>
                            {{ Form::textarea('urls_referencias', $programa->urls_referencias ?? '', ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Ingrese links de referencia']) }}
                        </div>
                    </div>

                    <div class="table-responsive">
                        @foreach ($categorias as $categoria)
                            <table class="table table-bordered table-hover table-sm table-program"
                                data-categoria-id="{{ $categoria->id }}"
                                data-es-exterior="{{ $categoria->es_exterior ? 'true' : 'false' }}">
                                <thead>
                                    <tr class="category-header bg-secondary text-white">
                                        <th colspan="9">{{ $categoria->nombre }}</th>
                                    </tr>
                                    <tr class="columns-header bg-dark text-white">
                                        <th>ESPACIO</th>
                                        <th>CANTIDAD</th>
                                        <th>ACTIVIDADES</th>
                                        <th>MOBILIARIO</th>
                                        <th>USUARIO</th>
                                        <th>M2</th>
                                        <th>OBSERVACIONES</th>
                                        <th>LINK REF</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- CAMBIO: Iteramos sobre los espacios que ya existen para esta categoría -->
                                    @if (isset($espaciosExistentes[$categoria->id]))
                                        @foreach ($espaciosExistentes[$categoria->id] as $espacio)
                                            <tr>
                                                <td><input type="text" name="espacios[{{ $espacio->id }}][espacio]"
                                                        class="form-control form-control-sm"
                                                        value="{{ $espacio->espacio }}">
                                                </td>
                                                <td><input type="number" name="espacios[{{ $espacio->id }}][cantidad]"
                                                        class="form-control form-control-sm"
                                                        value="{{ $espacio->cantidad }}">
                                                </td>
                                                <td>
                                                    <textarea name="espacios[{{ $espacio->id }}][actividades]" class="form-control form-control-sm" rows="1">{{ $espacio->actividades }}</textarea>
                                                </td>
                                                <td>
                                                    <textarea name="espacios[{{ $espacio->id }}][mobiliario]" class="form-control form-control-sm" rows="1">{{ $espacio->mobiliario }}</textarea>
                                                </td>
                                                <td><input type="text" name="espacios[{{ $espacio->id }}][usuario]"
                                                        class="form-control form-control-sm"
                                                        value="{{ $espacio->usuario }}">
                                                </td>
                                                <td><input type="number" step="0.01"
                                                        name="espacios[{{ $espacio->id }}][m2]"
                                                        class="form-control form-control-sm m2-input"
                                                        value="{{ $espacio->m2 }}">
                                                </td>
                                                <td>
                                                    <textarea name="espacios[{{ $espacio->id }}][observaciones]" class="form-control form-control-sm" rows="1">{{ $espacio->observaciones }}</textarea>
                                                </td>
                                                <td><input type="text" name="espacios[{{ $espacio->id }}][link_ref]"
                                                        class="form-control form-control-sm"
                                                        value="{{ $espacio->link_ref }}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm remove-row-btn"><i
                                                            class="fas fa-trash"></i></button>
                                                    <input type="hidden"
                                                        name="espacios[{{ $espacio->id }}][categoria_id]"
                                                        value="{{ $espacio->categoria_id }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <!-- El tbody puede empezar vacío si no hay espacios existentes -->
                                </tbody>
                                <tfoot>
                                    <tr class="subtotal-row font-weight-bold">
                                        <td colspan="5">SUBTOTAL M2</td>
                                        <td colspan="4"><span class="subtotal-m2">0.00</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-4 add-row-btn">+ Agregar Fila a
                                {{ $categoria->nombre }}</button>
                        @endforeach
                    </div>
                    <div class="totals-section">
                        <div
                            style="display: grid; grid-template-columns: 1fr auto; gap: 20px; width: 400px; margin-left: auto;">
                            <h5>TOTAL M2 AREA INTERIORES</h5>
                            <!-- Se añade el ID para que jQuery lo encuentre -->
                            <h5 id="total-interiores-val">
                                {{ number_format($espaciosExistentes->flatten()->where('categoria.es_exterior', false)->sum('m2'), 2) }}
                            </h5>
                        </div>
                        <div
                            style="display: grid; grid-template-columns: 1fr auto; gap: 20px; width: 400px; margin-left: auto;">
                            <h5>TOTAL M2 AREA EXTERIORES</h5>
                            <!-- Se añade el ID para que jQuery lo encuentre -->
                            <h5 id="total-exteriores-val">
                                {{ number_format($espaciosExistentes->flatten()->where('categoria.es_exterior', true)->sum('m2'), 2) }}
                            </h5>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </section>

    {{ Form::hidden('tipo_proforma', $tipo, ['id' => 'tipo-proforma']) }}

@endsection

@section('scripts')
    <script src="{{ asset('js/proformas.js?v=' . config('app.version', '')) }}"></script>
@endsection
