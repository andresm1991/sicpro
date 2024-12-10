<div class="card">
    <div class="card-body ">
        <div class="row">
            <div class="col-12 ">
                <div class="form-group form-search form-icon col-md-6 col-12 p-0">
                    <i class="fal fa-search fa-lg form-control-icon"></i>
                    <input type="text" name="orden_contratista_search" class="form-control form-control-round"
                        placeholder="Buscar....">
                </div>
            </div>
        </div>
        <div class="table-responsive" id="table">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th scope="col">Orden Nro.</th>
                        <th scope="col">Contratista</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Valor Contrato</th>
                        <th scope="col">Avance</th>
                        <th scope="col">Saldo</th>
                        <th class="col-accion"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orden_trabajos_completas as $orden_trabajo)
                        <tr id="{{ $orden_trabajo->id }}">
                            <td class="align-middle">
                                {{ numeroOrden($orden_trabajo, false) }}
                            </td>
                            <td class="align-middle text-uppercase">
                                {{ $orden_trabajo->proveedor->razon_social }}
                            </td>
                            <td class="align-middle text-uppercase">
                                {{ $orden_trabajo->articulo->descripcion }}
                            </td>
                            <td class="align-middle">
                                $ {{ number_format($orden_trabajo->total_contratistas, 2) }}
                            </td>
                            <td class="align-middle">
                                $ {{ number_format($orden_trabajo->pagos_contratistas, 2) }}
                            </td>
                            <td class="align-middle">
                                $
                                {{ number_format($orden_trabajo->total_contratistas - $orden_trabajo->pagos_contratistas, 2) }}
                            </td>
                            
                            <td class="align-middle text-right text-truncate">
                                <button type="button" class="btn btn-outline-dark" data-container="body"
                                    data-toggle="popover" data-placement="left" data-trigger="focus"
                                    data-content ="
                                    <a href='' class='dropdown-item'>Avances</a>
                                    <a href='' class='dropdown-item'>Editar</a>
                                    <a href='#' class='dropdown-item eliminar-orden-trabajo' id='{{ $orden_trabajo->id }}'>Eliminar</a>
                                    <a href='{{ route('pdf.orden.trabajo.contratista', $orden_trabajo->id) }}' class='dropdown-item' target='_blank'>PDF Orden Trabajo</a>">
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
        @include('partials.pagination', ['paginator' => $orden_trabajos_completas, 'interval' => 5])
    </div>
</div>