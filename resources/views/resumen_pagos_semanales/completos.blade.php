<div class="row">
    <div class="col-md-8 col-12 ">
        <div class="form-group form-search form-icon col-md-10 col-12  p-0">
            <i class="fal fa-search fa-lg form-control-icon"></i>
            <input type="text" name="resumen_pagos_search" id="completos" class="form-control form-control-round"
                placeholder="Buscar pago....">
        </div>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-hover" id="resumen_pagos_completos_table">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Fecha</th>
                <th scope="col">Total</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($resumen_pagos_completos as $resumen)
                <tr id="{{ $resumen->id }}">
                    <th class="align-middle">{{ $resumen->id }}</th>
                    <td class="align-middle">{{ dateFormat('Y-m-d', 'd-m-Y', $resumen->fecha) }}</td>
                    <td class="align-middle">$ {{ number_format($resumen->total, 4) }}</td>

                    <td class="align-middle table-actions">
                        <a href="javascript:void(0);" class="btn btn-dark btn-sm editar-resumen" data-toggle="modal"
                            data-backdrop="static" data-keyboard="false" data-target="#pagoSemanalModal"
                            id="{{ $resumen->id }}">
                            <i class="fa-light fa-edit"></i>
                        </a>
                        <a href="{{ route('pdf.resumen.pago.semanal', $resumen->id) }}" class="btn btn-dark btn-sm"
                            target="__blank">
                            <i class="fa-solid fa-file-pdf"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-dark btn-sm eliminar-resumen"
                            id="{{ $resumen->id }}">
                            <i class="fa-light fa-trash"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-danger">No se encontraron datos para
                        mostrar....
                    </td>
                </tr>
            @endforelse


        </tbody>
    </table>
</div>

@include('partials.pagination', ['paginator' => $resumen_pagos_completos, 'interval' => 5])
