@extends('layouts.app')

@section('title', $title_page)

@section('content')
    @include('partials.header_page')
    <section class="content" style="padding-bottom: 20px; margin:15px;">
        <div class="mi">
            @include('partials.alerts')
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            
                                @if ($tipo == 'administrativo')
                                    <div class="col-md-4 col-12">
                                        <div class="form-group">
                                            <a href="{{ route('administrativo.adquisiciones.create', $tipo) }}"
                                                class="btn btn-dark btn-sm mt-1">
                                                <i class="fa-regular fa-plus"></i> Nueva Adquisición
                                            </a>
                                        </div>  
                                    </div>  
                                @endif
                                
                            
                            <div class="col-md-8 col-12 ">
                                <div class="form-group form-search form-icon col-md-10 col-12 p-0 {{ $tipo == 'administrativo'? 'float-right' : '' }} ">
                                    <i class="fal fa-search fa-lg form-control-icon"></i>
                                    <input type="text" name="adquisicion_search" class="form-control form-control-round "
                                        placeholder="Ingresa el # para buscar....">
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-body ">
                    
                    <div class="table-responsive" id="table">
                        <table id="table-list-pedidos" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Proyecto</th>
                                    <th scope="col">Etapa</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Estado</th>
                                    <th class="col-accion"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adquisiciones as $adquisicion)
                                    <tr id="{{ $adquisicion->id }}">
                                        <td class="align-middle">{{ $adquisicion->numero }}</td>
                                        <td class="align-middle">{{ date('d-m-Y', strtotime($adquisicion->fecha)) }}</td>
                                        <td class="align-middle">{{ strtoupper($adquisicion->proyecto->nombre_proyecto) }}</td>
                                        <td class="align-middle">{{ strtoupper($adquisicion->etapa->descripcion) }}</td>
                                        <td class="align-middle">{{ strtoupper($adquisicion->tipo_etapa->descripcion)}}</td>
                                        <td class="align-middle">
                                            <span
                                                class="badge {{ $adquisicion->estado == 'Finalizado' ? 'badge-success' : 'badge-warning' }} ">{{ $adquisicion->estado }}</span>
                                        </td>
                                        <td class="align-middle align-middle text-right text-truncate">
                                            <button type="button" class="btn btn-outline-dark" data-container="body"
                                                data-toggle="popover" data-placement="left" data-trigger="focus"
                                                data-content ="
                                                    <a href='{{ route('administrativo.adquisicion.edit', ['tipo' => 'operativo','adquisicion' =>$adquisicion->id]) }}' class='dropdown-item'>Editar</a>
                                                    <a href='{{ route('pdf.recepcion', $adquisicion->id) }}' class='dropdown-item' target='_blank'>PDF Orden Recepción</a>
                                                ">
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
    
    <script src="{{ asset('js/administrativo_scripts.js') }}"></script>
@endsection
