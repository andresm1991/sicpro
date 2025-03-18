@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Presupuesto Referencial</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <div class="d-flex">
                                    <a href="javascript:void(0);" class="btn btn-dark btn-sm mr-2" data-toggle="modal"
                                        data-backdrop="static" data-keyboard="false" data-target="#modalRubrosPresupuesto">
                                        <i class="fa-light fa-plus"></i> Agregar Rubro
                                    </a>


                                    <a href="{{ route('pdf.export.presupuesto', $proyecto->id) }}"
                                        class="btn btn-secondary btn-sm mr-2" target="_blank">
                                        <i class="fa-light fa-file-export"></i> Exportar a PDF
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right  p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="rubros_search" data-proyecto_id="{{ $proyecto->id }}"
                                    class="form-control form-control-round" placeholder="Buscar por categoría o rubro...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="table-rubros-presupuesto">
                            <thead>
                                <tr>
                                    <th scope="col">Nro.</th>
                                    <th scope="col">Rubro</th>
                                    <th scope="col">UM</th>
                                    <th scope="col">Cantidad</th>
                                    <th scope="col">Costo</th>
                                    <th scope="col">Sub. Total</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $index = 1;
                                @endphp
                                @forelse ($categorias as $categoria)
                                    @php
                                        $total_categoria = 0;
                                    @endphp
                                    <tr id="categoria-{{ $categoria->id }}" data-categoria-id="{{ $categoria->id }}"
                                        class="fila-categoria" style="background-color: #b6bcdf;">
                                        <td colspan="6" class="align-middle font-weight-bold filtrable">
                                            {{ $categoria->nombre }}
                                        </td>
                                        <td class="align-middle">
                                            <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-categoria"
                                                data-id="{{ $categoria->id }}" data-proyecto_id="{{ $proyecto->id }}">
                                                <i class="fa-solid fa-trash-can-xmark"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @forelse ($categoria->rubrosPresupuesto as $rubro)
                                        @php
                                            $total_categoria += $rubro->presupuestoProyectos->sum(function ($item) {
                                                return $item->cantidad * $item->valor_unitario;
                                            });
                                        @endphp

                                        <tr data-categoria-id="{{ $categoria->id }}" class="fila-rubro">
                                            <td class="align-middle font-weight-bold">{{ $index }}</td>
                                            <td class="align-middle filtrable"> {{ $rubro->nombre }}
                                            </td>
                                            <td class="align-middle text-uppercase">
                                                {{ $rubro->unidad_medida->descripcion }}
                                            </td>
                                            @foreach ($rubro->presupuestoProyectos as $presupuestoProyecto)
                                                <td class="align-middle">
                                                    {{ $presupuestoProyecto->cantidad }}
                                                </td>
                                                <td class="align-middle">$ {{ $presupuestoProyecto->valor_unitario }}</td>
                                                <td class="align-middle">
                                                    $
                                                    {{ number_format($presupuestoProyecto->cantidad * $presupuestoProyecto->valor_unitario, 2) }}
                                                </td>


                                                <td class="align-middle table-actions">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-sm btn-secondary editar-rubro" data-toggle="modal"
                                                        data-backdrop="static" data-keyboard="false"
                                                        data-target="#modalRubrosPresupuesto"
                                                        id="{{ $presupuestoProyecto->id }}"
                                                        data-categoria_id="{{ $categoria->id }}"
                                                        data-unidad_medida_id = "{{ $rubro->unidad_medida_id }}"
                                                        data-etapa_id="{{ $rubro->etapa_id }}"
                                                        data-cantidad = "{{ $presupuestoProyecto->cantidad }}"
                                                        data-valor_unitario = "{{ $presupuestoProyecto->valor_unitario }}">
                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-rubro"
                                                        data-id="{{ $rubro->id }}">
                                                        <i class="fa-regular fa-xmark"></i>
                                                    </a>
                                                </td>
                                            @endforeach
                                        </tr>
                                        @php
                                            $index += 1;
                                        @endphp
                                    @empty
                                    @endforelse
                                    <tr style="background-color: #d7ecdc;" class="fila-total">
                                        <td colspan="5" class="font-weight-bold">
                                            Total General
                                        </td>
                                        <td colspan="2" class="font-weight-bold">
                                            $ {{ number_format($total_categoria, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7"></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">
                                            No se encontraron datos para mostrar....
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                @php
                                    $costos_directos = $categorias->sum(function ($item) {
                                        return $item->rubrosPresupuesto->sum(function ($item) {
                                            return $item->presupuestoProyectos->sum(function ($item) {
                                                return $item->cantidad * $item->valor_unitario;
                                            });
                                        });
                                    });

                                    $costos_indirectos = ($costos_directos * $proyecto->costo_indirecto) / 100;

                                    $saldo = $costos_directos - $total_gatos;
                                @endphp

                                <tr>
                                    <td colspan="5" class="font-weight-bold">
                                        <h4>COSTOS DIRECTOS</h4>
                                    </td>
                                    <td colspan="2" class="font-weight-bold">
                                        $
                                        {{ number_format($costos_directos, 2) }}
                                    </td>
                                </tr>
                                <tr style="background-color: #b6e5c1;">
                                    <td colspan="5" class="font-weight-bold editar-costo-indirecto"
                                        style="cursor: pointer;" data-id="{{ $proyecto->id }}"
                                        data-porcentaje="{{ $proyecto->costo_indirecto }}">
                                        <h4>COSTOS INDIRECTOS {{ $proyecto->costo_indirecto }}% </h4>
                                    </td>
                                    <td colspan="2" class="font-weight-bold">
                                        $
                                        {{ number_format($costos_indirectos, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="font-weight-bold">
                                        <h4>TOTAL</h4>
                                    </td>
                                    <td colspan="2" class="font-weight-bold">
                                        $
                                        {{ number_format($costos_directos + $costos_indirectos, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="5" class="font-weight-bold">
                                        <h4>gastos</h4>
                                    </td>
                                    <td colspan="2" class="font-weight-bold">
                                        $
                                        {{ number_format($total_gatos, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="5" class="font-weight-bold">
                                        <h4>SALDO</h4>
                                    </td>
                                    <td colspan="2" class="font-weight-bold">
                                        $
                                        {{ number_format($saldo, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <ul class="list-group list-group-flush mt-0 border-0">
                        <li class="list-group-item p-1 border-0">
                            <small>Para cambiar el % del <strong>COSTO INDIRECTO</strong> haz click sobre la fila y te
                                mostrar una pantalla donde debes ingresar el nuevo valor. </small>
                        </li>
                        <li class="list-group-item p-1 border-0">
                            <small>El valor del <strong class="text-uppercase">saldo</strong> es el calculo entre <strong
                                    class="text-uppercase">costos directos</strong> menos el total de los
                                <b class="text-uppercase">gastos</b> </small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    @include('modals.rubros_presupuesto_modal', [
        'unidades_medidas' => $unidades_medidas,
        'categorias_presupuesto' => $categorias_presupuesto,
        'etapas_construccion' => $etapas_construccion,
    ])

@endsection

@section('scripts')
    <script src="{{ asset('js/presupuesto_scripts.js') }}" type="module"></script>
@endsection
