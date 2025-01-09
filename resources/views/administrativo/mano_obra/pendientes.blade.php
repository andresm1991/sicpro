<div class="row">
    <div class="col-md-8 col-12 ">
        <div class="form-group form-search form-icon col-md-10 col-12 p-0">
            <i class="fal fa-search fa-lg form-control-icon"></i>
            <input type="text" name="mano_obra_search" id="pendiente" class="form-control form-control-round"
                placeholder="Buscar....">
        </div>
    </div>
</div>
<div class="table-responsive" id="table">
    <table class="table table-bordered table-hover" id="table_pendiente">
        <thead>
            <tr>
                <th style="width: 1px">Semana</th>
                <th scope="col">Proyecto</th>
                <th scope="col">Fecha</th>
                <th scope="col">Etapa</th>
                <th scope="col">Tipo proyecto</th>
                <th class="col-accion"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mano_obra_pendientes as $mano_obra)
                <tr id="{{ $mano_obra->id }}">
                    <td class="align-middle">{{ $mano_obra->semana }}</td>
                    <td class="align-middle">{{ strtoupper($mano_obra->proyecto->nombre_proyecto) }}</td>
                    <td
                        class="align-middle>{{ dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin) }}</td>
                    <td class="align-middle">
                        {{ $mano_obra->etapa->descripcion }}</td>
                    <td class="align-middle">
                        {{ $mano_obra->proyecto->tipo_proyecto->descripcion }}</td>
                    <td class="align-middle align-middle text-right text-truncate">
                        <a href="{{ route('administrativo.mano.obra.detalle', ['mano_obra' => $mano_obra->id, 'estado' => 'pendiente']) }}"
                            class="btn btn-outline-dark">
                            Detalle <i class="fas fa-caret-right font-weight-normal mx-2"></i>
                        </a>
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
@include('partials.pagination', ['paginator' => $mano_obra_pendientes, 'interval' => 5])
