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


                                    <a href="{{ route('pdf.jp.limpieza.presupuesto', $proyecto->id) }}"
                                        class="btn btn-secondary btn-sm mr-2" target="_blank">
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
                                    <tr class="categoria-padre fila-categoria" data-id="{{ $categoria->id }}"
                                        data-categoria-id="{{ $categoria->id }}" style="background-color: #b6bcdf;">
                                        <td colspan="7" class="align-middle font-weight-bold filtrable editar-categoria"
                                            style="cursor: pointer;">
                                            <span class="texto-categoria-descripcion">{{ $categoria->descripcion }}
                                            </span>
                                            <span class="texto-categoria-detalle">{{ $categoria->detalle }}
                                            </span>

                                            <div class="input-group edicion-categoria" style="display: none;">
                                                <input type="text" class="form-control input-categoria"
                                                    value="{{ $categoria->descripcion }}">
                                                <input type="text" class="form-control input-detalle"
                                                    value="{{ $categoria->detalle }}"
                                                    placeholder="ingrese detalle (opcional)">
                                                <div class="input-group-append">
                                                    <button class="btn btn-success btn-sm btn-guardar-categoria"
                                                        data-id="{{ $categoria->id }}">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm btn-cancelar-categoria">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @forelse ($categoria->hijos as $hijo)
                                        <tr class="categoria-hijo fila-rubro" data-id="{{ $hijo->id }}"
                                            data-categoria-id="{{ $categoria->id }}" data-padre-id="{{ $categoria->id }}">
                                            <td class="align-middle font-weight-bold">{{ $index }}</td>
                                            <td class="align-middle filtrable editar-rubro" style="cursor: pointer;">
                                                <span class="texto-rubro">{{ $hijo->descripcion }}</span>
                                                <div class="input-group edicion-rubro" style="display: none;">
                                                    <input type="text" class="form-control input-rubro"
                                                        value="{{ $hijo->descripcion }}">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-success btn-sm btn-guardar-rubro"
                                                            data-id="{{ $hijo->id }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm btn-cancelar-rubro">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
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
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-10 d-flex justify-content-end">
                            <label class="col-form-label">total contratado: </label>
                        </div>
                        <div class="col-2 d-flex justify-content-end">
                            <span class="col-form-label" id="total-estimado">
                                $ {{ $proyecto->total_contratado_formatted }}
                            </span>
                        </div>

                        <div class="col-10 d-flex justify-content-end">
                            <label class="col-form-label">total estimado: </label>
                        </div>
                        <div class="col-2 d-flex justify-content-end">
                            <span class="col-form-label" id="total-estimado">
                                $ {{ number_format($categorias->sum('total_categoria'), 4) }}
                            </span>
                        </div>

                        <div class="col-10 d-flex justify-content-end">
                            <label class="col-form-label">utilidad estimada: </label>
                        </div>
                        <div class="col-2 d-flex justify-content-end">
                            <span class="col-form-label" id="total-utilidad">
                                $ {{ number_format($proyecto->total_contratado - $categorias->sum('total_categoria'), 4) }}
                            </span>
                        </div>
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
    <script src="{{ asset('js/jp_limpieza/presupuesto.js?v=' . config('app.version', '')) }}" type="module"></script>
@endsection
