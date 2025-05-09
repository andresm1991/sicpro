@extends('layouts.app')

@section('title', 'Presupuesto')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Presupuesto</h4>
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


                                    <a href="{{ route('pdf.export.presupuesto', $proyecto['id']) }}"
                                        class="btn btn-secondary btn-sm mr-2" target="_blank">
                                        <i class="fa-light fa-file-export"></i> Exportar a PDF
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right  p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="rubros_search" data-proyecto_id="{{ $proyecto['id'] }}"
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
                                    <th scope="col">Cantidad</th>
                                    <th scope="col">Valor unit.</th>
                                    <th scope="col">Sub. Total</th>
                                    <th scope="col">meses</th>
                                    <th scope="col">total</th>
                                    <th class="table-actions"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $index = 1;
                                @endphp
                                @forelse ($proyecto['presupuesto_agrupado'] as $presupuesto)
                                    @php
                                        $total_categoria = 0;
                                    @endphp
                                    <tr id="categoria-{{ $presupuesto['categoria_id'] }}"
                                        data-categoria-id="{{ $presupuesto['categoria_id'] }}" class="fila-categoria"
                                        style="background-color: #b6bcdf;">
                                        <td colspan="7" class="align-middle font-weight-bold filtrable">
                                            {{ $presupuesto['categoria_nombre'] }}
                                        </td>
                                        <td class="align-middle">
                                            <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-categoria"
                                                data-id="{{ $presupuesto['categoria_id'] }}"
                                                data-proyecto_id="{{ $proyecto['id'] }}">
                                                <i class="fa-solid fa-trash-can-xmark"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @forelse ($presupuesto['rubros'] as $rubro)
                                        <tr data-categoria-id="{{ $presupuesto['categoria_id'] }}" class="fila-rubro">
                                            <td class="align-middle font-weight-bold">{{ $index }}</td>
                                            <td class="align-middle filtrable"> {{ $rubro['rubro'] }}</td>
                                            <td class="align-middle">
                                                {{ $rubro['detalles']['cantidad'] }}
                                            </td>
                                            <td class="align-middle">$
                                                {{ number_format($rubro['detalles']['precio_unitario'], 4) }}</td>
                                            <td class="align-middle">
                                                $
                                                {{ number_format($rubro['detalles']['subtotal'], 4) }}
                                            </td>
                                            <td class="align-middle">
                                                {{ $rubro['detalles']['meses'] }}
                                            </td>

                                            <td class="align-middle">
                                                $
                                                {{ number_format($rubro['detalles']['total_sin_iva'], 4) }}
                                            </td>


                                            <td class="align-middle table-actions">
                                                <a href="javascript:void(0);" class="btn btn-sm btn-secondary editar-rubro"
                                                    data-toggle="modal" data-backdrop="static" data-keyboard="false"
                                                    data-target="#modalRubrosPresupuesto"
                                                    id="{{ $rubro['detalles']['id'] }}"
                                                    data-categoria_id="{{ $presupuesto['categoria_id'] }}"
                                                    data-cantidad = "{{ $rubro['detalles']['cantidad'] }}"
                                                    data-valor_unitario = "{{ $rubro['detalles']['precio_unitario'] }}"
                                                    data-meses = "{{ $rubro['detalles']['meses'] }}">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-rubro"
                                                    data-id="{{ $rubro['detalles']['id'] }}">
                                                    <i class="fa-regular fa-xmark"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @php
                                            $index += 1;
                                        @endphp
                                    @empty
                                    @endforelse
                                    <tr style="background-color: #d7ecdc;" class="fila-total">
                                        <td colspan="6" class="font-weight-bold">
                                            Total General
                                        </td>
                                        <td colspan="2" class="font-weight-bold">
                                            $ {{ number_format($proyecto['totales']['subtotal'], 4) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-danger">
                                            No se encontraron datos para mostrar....
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            {{-- 
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
                             --}}
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('jp_limpieza.presupuesto.agregar_rubro_modal')
@endsection

@section('scripts')
    <script>
        var presupuestoUrl = "{{ route('jp.limpieza.presupuesto.index', $proyecto['id']) }}";
    </script>
    <script src="{{ asset('js/jp_limpieza/presupuesto.js') }}" type="module"></script>
@endsection
