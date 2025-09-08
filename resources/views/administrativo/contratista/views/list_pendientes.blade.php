<div class="card">
    <div class="card-body ">
        <div class="row">
            <div class="col-12 ">
                <div class="form-group form-search form-icon col-md-6 col-12 p-0">
                    <i class="fal fa-search fa-lg form-control-icon"></i>
                    <input type="text" name="orden_contratista_search" id="proceso"
                        class="form-control form-control-round"
                        placeholder="Ingresa contratista o nro orden para buscar">
                </div>
            </div>
        </div>
        <div class="table-responsive" id="table-proceso">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th scope="col">Orden Nro.</th>
                        <th scope="col">proyecto</th>
                        <th scope="col">Contratista</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Valor Contrato</th>
                        <th scope="col">Avance</th>
                        <th scope="col">Saldo</th>
                        <th class="col-accion"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orden_trabajos_pendientes as $orden_trabajo)
                        <tr id="{{ $orden_trabajo->id }}"
                            class="{{ $orden_trabajo->pagosOrdenTrabajoContratista->contains('pagado', false) ? 'table-warning' : 'clase-no-existe' }}">
                            <td class="align-middle">
                                {{ numeroOrden($orden_trabajo, false) }}
                            </td>
                            <td class="align-middle text-uppercase">
                                {{ $orden_trabajo->proyecto->nombre_proyecto }}
                            </td>
                            <td class="align-middle text-uppercase">
                                {{ $orden_trabajo->proveedor->razon_social }}
                            </td>
                            <td class="align-middle text-uppercase">
                                {{ $orden_trabajo->articulo->descripcion }}
                            </td>
                            <td class="align-middle">
                                $ {{ number_format($orden_trabajo->total_contratistas, 4) }}
                            </td>
                            <td class="align-middle">
                                $ {{ number_format($orden_trabajo->pagos_contratistas, 4) }}
                            </td>
                            <td class="align-middle">
                                $
                                {{ number_format($orden_trabajo->total_contratistas - $orden_trabajo->pagos_contratistas, 4) }}
                            </td>

                            <td class="align-middle align-middle text-right text-truncate">
                                <button type="button" class="btn btn-outline-dark" data-container="body"
                                    data-toggle="popover" data-placement="left" data-trigger="focus"
                                    data-content ="<a href='{{ route('administrativo.contratista.editar', $orden_trabajo->id) }}' class='dropdown-item'>Editar</a>
                                    <a href='{{ route('administrativo.contratista.detalle', $orden_trabajo->id) }}' class='dropdown-item'>Pagos</a>
                                    <a href='{{ route('pdf.orden.trabajo.contratista', $orden_trabajo->id) }}' class='dropdown-item' target='_blank'>PDF</a> ">
                                    <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                </button>
                            </td>
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
        @include('partials.pagination', ['paginator' => $orden_trabajos_pendientes, 'interval' => 5])
    </div>
</div>
