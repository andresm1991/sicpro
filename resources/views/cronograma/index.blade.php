@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
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
                                    class="form-control form-control-round" placeholder="Buscar rubro....">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="table-rubros-presupuesto">
                            <thead>
                                <tr>
                                    <th scope="col">Nro.</th>
                                    <th scope="col">Rubro </th>
                                    @for ($i = 0; $i < $plazo_semanas; $i++)
                                        <th>{{ $i +1 }}</th>    
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categorias as $categoria)
                                    @php
                                        $total_categoria = 0;
                                    @endphp
                                    @forelse ($categoria->rubrosPresupuesto as $index => $rubro)
                                        @php
                                            $total_categoria += $rubro->presupuestoProyectos->sum(function ($item) {
                                                return $item->cantidad * $item->valor_unitario;
                                            });
                                        @endphp
                                        <tr>
                                            <td class="align-middle font-weight-bold">{{ $index + 1 }}</td>
                                            <td class="align-middle"> {{ $rubro->nombre }}</td>
                                            @for ($i = 0; $i < $plazo_semanas; $i++)
                                                <td></td>
                                            @endfor
                                        </tr>
                                    @empty
                                    
                                    @endforelse
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-danger">
                                            No se encontraron datos para mostrar....
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/presupuesto_scripts.js') }}" type="module"></script>
@endsection
