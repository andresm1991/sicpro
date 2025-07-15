@extends('layouts.app')

@section('title', $tipoAdquisicion->descripcion ?? 'Adquisiciones')

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="mi">
            @include('partials.alerts')
            <div class="card">
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                @isset($proyecto)
                                    <a href="{{ route('jp.limpieza.adquisiciones.create', [$proyecto, $tipoAdquisicion->slug]) }}"
                                        class="btn btn-dark btn-sm">
                                        <i class="fa-regular fa-plus"></i> Nuevo Pedido
                                    </a>
                                @else
                                    <a href="{{ route('jp.limpieza.adquisiciones.administrativas.create') }}"
                                        class="btn btn-dark btn-sm">
                                        <i class="fa-regular fa-plus"></i> Nuevo Pedido
                                    </a>
                                @endisset

                            </div>
                        </div>
                        <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="buscar-adquisicion" id="buscar-adquisicion"
                                    data-tipo="{{ isset($proyecto) ? 'operativo' : 'administrativo' }}"
                                    class="form-control form-control-round" placeholder="Buscar....">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table">
                        <table id="table-list-pedidos" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Fecha Pedido</th>
                                    <th scope="col">Forma Pago</th>
                                    <th scope="col">Proveedor</th>
                                    <th scope="col">Estado</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adquisiciones as $adquisicion)
                                    <tr id="{{ $adquisicion->id }}">
                                        <td class="align-middle">{{ $adquisicion->numero }}</td>
                                        <td class="align-middle">{{ date('d-m-Y', strtotime($adquisicion->fecha)) }}</td>
                                        <td class="align-middle">{{ $adquisicion->formaPago->descripcion }}</td>
                                        <td class="align-middle">{{ $adquisicion->proveedor->razon_social }}</td>
                                        <td class="align-middle">
                                            @if (preg_match('/\b(completado|finalizado)\b/i', $adquisicion->estado))
                                                <span class="badge badge-success ">Finalizado</span>
                                            @else
                                                <span class="badge badge-warning ">{{ $adquisicion->estado }}</span>
                                            @endif

                                        </td>
                                        <td class="align-middle text-right text-truncate">
                                            <button type="button" class="btn btn-outline-dark" data-container="body"
                                                data-toggle="popover" data-placement="left" data-trigger="focus"
                                                data-content ="@isset($proyecto)
                                                <a href='{{ route('jp.limpieza.adquisiciones.edit', ['proyecto' => $proyecto, 'tipo_adquisicion' => $tipoAdquisicion->slug, 'adquisicion' => $adquisicion->id]) }}' class='dropdown-item'>Editar</a>
                                                @else
                                                <a href='{{ route('jp.limpieza.adquisiciones.administrativas.edit', $adquisicion->id) }}' class='dropdown-item'>Editar</a>
                                                @endisset
                                                
                                                <a href='javascript:void(0);' class='dropdown-item eliminar-adquisicion' id='{{ $adquisicion->id }}'>Eliminar</a>
                                                <a href='{{ route('pdf.recepcion', $adquisicion->id) }}' class='dropdown-item' target='_blank'>Generar PDF</a> ">
                                                <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">No se encontraron datos para
                                            mostrar....
                                        </td>
                                    </tr>
                                @endforelse


                            </tbody>
                        </table>
                    </div>
                    @include('partials.pagination', ['paginator' => $adquisiciones, 'interval' => 5])
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')

    <script>
        var url =
            "{{ isset($proyecto) ? route('jp.limpieza.adquisiciones.tipo.adquisicion', ['proyecto' => $proyecto, 'tipo_adquisicion' => $tipoAdquisicion->slug]) : route('jp.limpieza.adquisiciones.administrativas.index') }}";
    </script>


    <script src="{{ asset('js/jp_limpieza/adquisiciones.js?v=' . config('app.version', '')) }}"></script>
@endsection
