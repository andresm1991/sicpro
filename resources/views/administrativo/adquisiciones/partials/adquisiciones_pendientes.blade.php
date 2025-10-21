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
                <div
                    class="form-group form-search form-icon col-md-10 col-12 p-0 {{ $tipo == 'administrativo' ? 'float-right' : '' }} ">
                    <i class="fal fa-search fa-lg form-control-icon"></i>
                    <input type="text" name="adquisicion_search" id="pendientes"
                        class="form-control form-control-round " placeholder="Ingresa el # para buscar....">
                </div>
            </div>
        </div>
    </li>
</ul>
<div class="table-responsive" id="table">
    <table id="table-list-pedidos-pendientes" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Fecha</th>
                <th scope="col">Proyecto</th>
                <th scope="col">SubProyecto</th>
                <th scope="col">Etapa</th>
                <th scope="col">Tipo</th>
                <th class="col-accion"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($adquisiciones_pendientes as $adquisicion)
                <tr id="{{ $adquisicion->id }}">
                    <td class="align-middle">{{ $adquisicion->numero }}</td>
                    <td class="align-middle">{{ date('d-m-Y', strtotime($adquisicion->fecha)) }}</td>
                    <td class="align-middle">
                        {{ $adquisicion->proyecto_id > 0 ? strtoupper($adquisicion->proyecto->nombre_proyecto) : 'GENERAL' }}
                    </td>
                    <td class="align-middle">
                        {{ $adquisicion->proyecto_id > 0 && $adquisicion->subproyecto ? $adquisicion->subproyecto : '---' }}
                    </td>
                    <td class="align-middle">{{ strtoupper($adquisicion->etapa->descripcion) }}</td>
                    <td class="align-middle">{{ strtoupper($adquisicion->tipo_etapa->descripcion) }}</td>

                    <td class="align-middle align-middle text-right text-truncate">
                        <button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover"
                            data-placement="left" data-trigger="focus"
                            data-content ="
                        <a href='{{ route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id]) }}' class='dropdown-item'>Editar</a>
                        <a href='#' class='dropdown-item eliminar-adquisicion' id='{{ $adquisicion->id }}'>Eliminar</a>
                        <a href='{{ route('pdf.recepcion', $adquisicion->id) }}' class='dropdown-item' target='_blank'>Generar PDF</a>
                    ">
                            <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                        </button>
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
@include('partials.pagination', ['paginator' => $adquisiciones_pendientes, 'interval' => 5])
