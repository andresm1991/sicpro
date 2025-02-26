@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    @include('partials.alerts')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h4 class="mt-2 font-weight-bold">Cronograma</h4>
                    </li>
                </ul>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <div class="d-flex">
                                    <a href="javascript:void(0);" class="btn btn-dark btn-sm mr-2" data-toggle="modal"
                                        data-backdrop="static" data-keyboard="false" data-target="#modalRubroCronograma">
                                        <i class="fa-light fa-plus"></i> Agregar Actividad
                                    </a>
                                    <a href="{{ route('pdf.export.cronograma', $proyecto->id) }}"
                                        class="btn btn-secondary btn-sm mr-2" target="_blank">
                                        <i class="fa-light fa-file-export"></i> Exportar a PDF
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{--  <div class="col-md-8 col-12 ">
                            <div class="form-group form-search form-icon col-md-10 col-12 float-right  p-0">
                                <i class="fal fa-search fa-lg form-control-icon"></i>
                                <input type="text" name="rubros_search" data-proyecto_id="{{ $proyecto->id }}"
                                    class="form-control form-control-round" placeholder="Buscar rubro....">
                            </div>
                        </div> --}}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="table-rubros-cronograma">
                            <thead>
                                <tr>
                                    <th scope="col">Nro.</th>
                                    <th scope="col">Actividades </th>
                                    @for ($i = 1; $i <= $plazo_semanas; $i++)
                                        <th class="text-center" data-semana={{ $i }}>
                                            <a
                                                href="{{ route('proyecto.cronograma.actividades.semana', ['proyecto' => $proyecto, 'semana' => $i]) }}">{{ $i }}</a>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $nro = 1;
                                @endphp
                                @forelse ($cronograma as $index => $rubro)
                                    <tr id="{{ $index }}">
                                        <td class="aling-middle">{{ $nro }}</td>
                                        <td class="aling-middle">{{ $rubro['rubro_cronograma_nombre'] }}</td>
                                        @for ($i = 1; $i <= $plazo_semanas; $i++)
                                            <td class="aling-middle text-center editar-rubro {{ isset($rubro['semanas'][$i]) ? 'pintado_pendiente' : '' }}"
                                                data-dias="{{ isset($rubro['semanas'][$i]) ? implode(', ', $rubro['semanas'][$i]) : '' }}"
                                                data-rubro="{{ isset($rubro['semanas'][$i]) ? $rubro['rubro_cronograma_id'] : '' }}"
                                                data-semana="{{ isset($rubro['semanas'][$i]) ? $i : '' }}"
                                                style="cursor:pointer;">
                                                @if (isset($rubro['semanas'][$i]))
                                                    {{ $nro }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                    @php
                                        $nro += 1;
                                    @endphp
                                @empty
                                    <tr>
                                        <td colspan="{{ $plazo_semanas + 2 }}" class="text-center">No existen datos para
                                            mostrar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <table class="table table-bordered ">
                                    <tr>
                                        <th>Totales</th>
                                        <th class="text-center">etapa ESTRUCTURAl</th>
                                        <th class="text-center">Etapa OBRA GRIS</th>
                                        <th class="text-center">ETAPA DE ACABADOS</th>
                                    </tr>

                                    <tr>
                                        <th>presupuesto</th>
                                        <td class="text-center">
                                            $ {{ number_format($total_estructural, 4) }}
                                        </td>
                                        <td class="text-center">
                                            $ {{ number_format($total_mpel, 4) }}
                                        </td>
                                        <td class="text-center">
                                            $ {{ number_format($total_acabados, 4) }}
                                        </td>
                                    </tr>
                                    {{-- totales adquisisicones, mano de obra, contratista --}}
                                    <tr>
                                        <th>adquisiciones</th>
                                        <td class="aling-middle text-center">
                                            $ {{ number_format($totales_estructural['totalAdquisiciones'], 4) }}
                                        </td>
                                        <td class="aling-middle text-center">
                                            $ {{ number_format($totales_estructural['totalManoObra'], 4) }}
                                        </td>
                                        <td class="aling-middle text-center">
                                            $ {{ number_format($totales_estructural['totalContratista'], 4) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            $ {{ number_format($total, 4) }}
                                        </td>
                                    </tr>

                                </table>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('modals.rubro_cronograma_modal', [
    'plazo_semanas',
    $plazo_semanas,
    'rubros_cronograma',
    $rubros_cronograma,
])

@section('scripts')
    <script src="{{ asset('js/cronograma_scripts.js') }}" type="module"></script>
@endsection
