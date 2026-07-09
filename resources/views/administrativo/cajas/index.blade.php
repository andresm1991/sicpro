@extends('layouts.app')

@section('title', 'Flujo de Caja')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        @include('partials.alerts')
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h4 class="mt-2 font-weight-bold">Flujo de caja</h4>
                </li>
            </ul>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-dark btn-sm" data-caja="prime" data-toggle="modal"
                                data-backdrop="static" data-keyboard="false" data-target="#modalMovimientoCaja">
                                <i class="fa-regular fa-plus"></i> Nueva Registro
                                </a>
                        </div>
                    </div>
                    {{-- <div class="col-md-8 col-12 ">
                        <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                            <i class="fal fa-search fa-lg form-control-icon"></i>
                            <input type="text" name="mano_obra_search" class="form-control form-control-round"
                                placeholder="Buscar....">
                        </div>
                    </div>
                    --}}
                </div>
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Proyecto</th>
                                <th>SubProyecto</th>
                                <th scope="col">descripcion</th>
                                <th scope="col">necesidad</th>
                                <th scope="col">proveedor</th>
                                <th scope="col">documento</th>
                                <th scope="col">tipo</th>
                                <th scope="col">monto</th>
                                <th scope="col">saldo</th>
                                @can('caja.eliminar')
                                    <th scope="col-accion"></th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movimientos as $movimiento)
                                <tr id="{{ $movimiento->id }}">
                                    <td class="align-middle">
                                        {{ $movimiento->fecha_formateada }}
                                    </td>
                                    <td class="align-middle">
                                        {{ isset($movimiento->origen_id) && $movimiento->origen_type == 'App\Models\Adquisicion' ? $movimiento->adquisicion->proyecto->nombre_proyecto : '' }}
                                    </td>
                                    <td class="align-middle">
                                        {{ isset($movimiento->origen_id) ? $movimiento->adquisicion->subproyecto : '' }}
                                    </td>
                                    <td class="align-middle">
                                        {{ isset($movimiento->articulo_id) ? $movimiento->articulo->descripcion : $movimiento->descripcion }}
                                    </td>

                                    <td class="align-middle">
                                        {{ isset($movimiento->articulo_id) ? $movimiento->descripcion : '-' }}
                                    </td>

                                    <td class="align-middle">
                                        {{ isset($movimiento->proveedor) ? $movimiento->proveedor->razon_social : '-' }}
                                    </td>

                                    <td class="align-middle">
                                        {{ $movimiento->referencia ?? '-' }}
                                    </td>

                                    <td class="align-middle">
                                        <span
                                            class="badge {{ $movimiento->tipo == 'ingreso' ? 'badge-success' : 'badge-danger' }}">{{ $movimiento->tipo }}</span>

                                    </td>
                                    <td class="align-middle">
                                        {{ $movimiento->monto_formatted }}
                                    </td>
                                    <td class="align-middle">
                                        ${{ number_format($movimiento->saldo_acumulado, 4) }}
                                    </td>
                                    @can('caja.eliminar')
                                        <td class="align-middle">
                                            <button type="button" class="btn btn-danger btn-sm eliminarMovimiento"
                                                data-id="{{ $movimiento->id }}">
                                                <i class="fa-regular fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-danger">No se encontraron datos para
                                        mostrar....
                                    </td>
                                </tr>
                            @endforelse


                        </tbody>
                    </table>
                </div>
                @include('partials.pagination', ['paginator' => $movimientos, 'interval' => 5])
            </div>
        </div>
        @include('administrativo.cajas.modal_formulario_registro')
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/caja.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection