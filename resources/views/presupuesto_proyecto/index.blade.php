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
                                <a href="javascript:void(0);" class="btn btn-dark btn-sm" data-toggle="modal"
                                    data-backdrop="static" data-keyboard="false" data-target="#modalRubrosPresupuesto">
                                    <i class="fa-light fa-plus"></i> Agregar Rubro
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="proveedor_search" class="form-control form-control-round"
                                    placeholder="Buscar rubro....">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
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
                                @forelse ($categorias as $categoria)
                                    @php
                                        $total_categoria = 0;
                                    @endphp
                                    <tr id="{{ $categoria->id }}" style="background-color: #b6bcdf;">
                                        <td colspan="7" class="align-middle"><strong>{{ $categoria->nombre }}</strong>
                                        </td>
                                    </tr>
                                    @forelse ($categoria->rubrosPresupuesto as $index => $rubro)
                                        @php
                                            $total_categoria += $rubro->presupuestoProyectos->sum(function ($item) {
                                                return $item->cantidad * $item->valor_unitario;
                                            });
                                        @endphp
                                        <tr>
                                            <td class="align-middle font-weight-bold">{{ $index + 1 }}</td>
                                            <td class="align-middle"> {{ $rubro->nombre }}</td>
                                            <td class="align-middle text-uppercase">{{ $rubro->unidad_medida->descripcion }}
                                            </td>
                                            @foreach ($rubro->presupuestoProyectos as $presupuestoProyecto)
                                                <td class="align-middle">{{ $presupuestoProyecto->cantidad }}</td>
                                                <td class="align-middle">$ {{ $presupuestoProyecto->valor_unitario }}</td>
                                                <td class="align-middle">
                                                    $
                                                    {{ number_format($presupuestoProyecto->cantidad * $presupuestoProyecto->valor_unitario, 2) }}
                                                </td>
                                            @endforeach

                                            <td class="align-middle"></td>
                                        </tr>
                                    @empty
                                    @endforelse
                                    <tr style="background-color: #d7ecdc;">
                                        <td colspan="5" class="font-weight-bold">
                                            Total General
                                        </td>
                                        <td colspan="2" class="font-weight-bold">
                                            $ {{ number_format($total_categoria, 2) }}</td>
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
                                @endphp
                                <tr>
                                    <td colspan="7"></td>
                                </tr>
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
                                    <td colspan="5" class="font-weight-bold">
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
                            </tfoot>
                        </table>
                    </div>

                    <ul class="list-group list-group-flush mt-0">
                        <li class="list-group-item p-1">
                            <small>Para cambiar el % del <strong>COSTO INDIRECTO</strong> haz click sobre la fila y te
                                mostrar una pantalla donde debes ingresar el nuevo valor. </small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    @include('modals.rubros_presupuesto_modal', [
        'unidades_medidas' => $unidades_medidas,
        'categorias_presupuesto' => $categorias_presupuesto,
    ])

@endsection

@section('scripts')
    <script src="{{ asset('js/presupuesto_scripts.js') }}" type="module"></script>
@endsection
