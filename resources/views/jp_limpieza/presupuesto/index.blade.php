@extends('layouts.app')

@section('title', 'Presupuesto')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            @include('partials.alerts')
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row d-flex justify-content-center align-items-center">
                            <h4 class="col ">PRESUPUESTO</h4>
                            <div class="col text-right">
                                <button class="btn btn-dark btn-options" form="form_presupuesto">Guardar</button>
                            </div>
                        </div>
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


                                    <a href="" class="btn btn-secondary btn-sm mr-2" target="_blank">
                                        <i class="fa-light fa-file-export"></i> Exportar a PDF
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right  p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="rubros_search" data-proyecto_id=""
                                    class="form-control form-control-round" placeholder="Buscar por categoría o rubro...">
                            </div>
                        </div>
                    </div>

                    {{ Form::open([
                        'route' => ['jp.limpieza.presupuesto.store', $proyecto->id],
                        'class' => 'form-horizontal',
                        'autocomplete' => 'off',
                        'enctype' => 'multipart/form-data',
                        'id' => 'form_presupuesto',
                    ]) }}
                    <div class="table-responsive fixed-header">
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
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $index = 1;
                                @endphp
                                @forelse ($categorias as $categoria)
                                    <tr class="categoria-padre" data-id="{{ $categoria->id }}"
                                        style="background-color: #b6bcdf;">
                                        <td colspan="7" class="align-middle font-weight-bold filtrable">
                                            {{ $categoria->descripcion . ' ' . $categoria->detalle }}
                                        </td>
                                    </tr>
                                    @forelse ($categoria->hijos as $hijo)
                                        <tr class="categoria-hijo" data-id="{{ $hijo->id }}"
                                            data-padre-id="{{ $categoria->id }}">
                                            <td class="align-middle font-weight-bold">{{ $index }}</td>
                                            <td class="align-middle filtrable"> {{ $hijo->descripcion }}</td>
                                            <td class="align-middle">
                                                {{ Form::text('presupuesto[' . $hijo->id . '][cantidad]', old('cantidad', $hijo->presupuestoProyecto->cantidad ?? 0), ['class' => 'form-control form-control-sm col-auto input-double cantidad', 'data-id' => $hijo->id]) }}

                                            </td>
                                            <td class="align-middle">
                                                {{ Form::text('presupuesto[' . $hijo->id . '][precio_unitario]', old('precio_unitario', $hijo->presupuestoProyecto->precio_unitario ?? 0), ['class' => 'form-control form-control-sm col-auto money precio_unitario', 'data-id' => $hijo->id]) }}
                                            </td>
                                            <td class="align-middle subtotal" data-id="{{ $hijo->id }}">
                                                $
                                                {{ number_format(
                                                    ($hijo->presupuestoProyecto->cantidad ?? 1) * ($hijo->presupuestoProyecto->precio_unitario ?? 0),
                                                    4,
                                                ) }}
                                            </td>
                                            <td class="align-middle">
                                                {{ Form::text('presupuesto[' . $hijo->id . '][meses]', old('meses', $hijo->presupuestoProyecto->meses ?? 0), ['class' => 'form-control form-control-sm col-auto input-enteros meses', 'data-id' => $hijo->id]) }}
                                            </td>
                                            <td class="align-middle total" data-id="{{ $hijo->id }}">
                                                ${{ number_format(
                                                    ($hijo->presupuestoProyecto->cantidad ?? 0) *
                                                        ($hijo->presupuestoProyecto->precio_unitario ?? 0) *
                                                        ($hijo->presupuestoProyecto->meses ?? 0),
                                                    4,
                                                ) }}
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
                                            {{ $categoria->total_categoria_formatted }}
                                        </td>
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
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </section>

    @include('jp_limpieza.presupuesto.agregar_rubro_modal')
@endsection

@section('scripts')
    <script>
        var presupuestoUrl = "{{ route('jp.limpieza.presupuesto.index', $proyecto->id) }}";
    </script>
    <script src="{{ asset('js/jp_limpieza/presupuesto.js') }}" type="module"></script>
@endsection
