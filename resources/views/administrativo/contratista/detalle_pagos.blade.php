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
                                <th scope="col" class="text-center">Pagado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($detalle_pagos as $index => $pagos)
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
                                        $ {{ number_format($pagos->valor, 2) }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $pagos->detalle }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="checkbox-wrapper-8 d-flex justify-content-center align-items-center">
                                            {{ Form::hidden('pagado[' . $index . ']', 0) }}
                                            <input class="tgl tgl-skewed pagado" name="pagado[{{ $index }}]"
                                                id="cb3-{{ $index }}" type="checkbox" value="{{ $pagos->id }}"
                                                {{ $pagos->pagado == true ? 'checked' : '' }}
                                                {{ $pagos->pagado == true ? 'disabled' : '' }} />
                                            <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI"
                                                for="cb3-{{ $index }}"></label>
                                        </div>
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
                @include('partials.pagination', ['paginator' => $detalle_pagos, 'interval' => 5])
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    <script>
        var url =
            "{{ route('administrativo.contratista.detalle', $contratista->id) }}";
    </script>
    <script src="{{ asset('js/administrativo_scripts.js') }}" type="module"></script>
@endsection
