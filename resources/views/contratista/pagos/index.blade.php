@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        @include('partials.alerts')
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h4 class="mt-2 font-weight-bold">Pagos Ordenes de Trabajo</h4>
                </li>
            </ul>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <a href="{{ route('proyecto.adquisiciones.contratista.nuevo.pago.orden.trabajo',['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id, 'contratista' => $contratista]) }}" class="btn btn-dark btn-sm">
                                <i class="fa-regular fa-plus"></i> Nueva pago
                            </a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Pago Nro.</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Pago</th>
                                <th scope="col">Valor</th>
                                <th scope="col">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pagos_orden_trabajos as $pagos)
                                <tr id="{{ $pagos->id }}">
                                    <td class="align-middle">
                                        {{ numeroOrden($pagos, false) }}
                                    </td>
                                    <td class="align-middle text-uppercase">
                                        {{ $pagos->fecha }}
                                    </td>
                                    <td class="align-middle text-capitalize">
                                        {{ $pagos->tipo_pago }}
                                    </td>
                                    <td class="align-middle text-capitalize">
                                        {{ $pagos->forma_pago }}
                                    </td>
                                    <td class="align-middle">
                                        $ {{ number_format($pagos->valor,2) }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $pagos->detalle }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-danger">No se encontraron datos para
                                        mostrar....
                                    </td>
                                </tr>
                            @endforelse
    
    
                        </tbody>
                    </table>
                </div>
                @include('partials.pagination', ['paginator' => $pagos_orden_trabajos, 'interval' => 5])
            </div>
        </div>
    </section>
   
@endsection

@section('scripts')
    <script>
        var url =
            "{{ route('proyecto.adquisiciones.contratista', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_etapa' => $tipo_etapa->id, 'tipo_adquisicion' => $tipo_adquisicion->id]) }}";
    </script>
    <script src="{{ asset('js/orden_trabajo_contratistas_scripts.js') }}" type="module"></script>
@endsection

