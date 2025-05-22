<div class="table-responsive">

    <table class="table table-bordered table-hover table-sm" id="tabla-planificacion">
        <thead>
            <tr>
                <th scope="col">Personal</th>
                <th scope="col">sueldo</th>
                <th scope="col">horas extras</th>
                <th scope="col">total ganado</th>
                <th scope="col">fondos</th>
                <th scope="col">decimo tercer sueldo</th>
                <th scope="col">decimo cuarto sueldo</th>
                <th scope="col">total ingresos</th>
                <th scope="col">i.e.s.s</th>
                <th scope="col">atrasos y faltas</th>
                <th scope="col">anticipos</th>
                <th scope="col">Prestamo iess</th>
                <th scope="col">quincena</th>
                <th scope="col">prestamo jp</th>
                <th scope="col">total descuentos</th>
                <th scope="col">total a recibir</th>
                <th class="table-actions"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ultimosDetalles as $personal)
                <tr class="elementos-agregados">
                    <td>
                        {{ $personal->proveedor->nombres }} {{ $personal->proveedor->apellidos }}
                        <input type="hidden" name="proveedor[]" value="{{ $personal->proveedor_id }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->sueldo_formatted }}</span>
                        <input type="hidden" name="sueldo[]" value="{{ $personal->sueldo }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->horas_extras_formatted }}</span>
                        <input type="hidden" name="h_extras[]" value="{{ $personal->horas_extras }}">
                    </td>
                    <td>
                        <span>{{ $personal->total_ganado_formatted }}</span>
                        <input type="hidden" name="total_ganado[]" value="{{ $personal->total_ganado }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->fondo_formatted }}</span>
                        <input type="hidden" name="fondos[]" value="{{ $personal->fondos }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->decimo_tercero_formatted }}</span>
                        <input type="hidden" name="decimo_tercero[]" value="{{ $personal->decimo_tercero }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->decimo_cuarto_formatted }}</span>
                        <input type="hidden" name="decimo_cuarto[]" value="{{ $personal->decimo_cuarto }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->total_ingresos_formatted }}</span>
                        <input type="hidden" name="total_ingresos[]" value="{{ $personal->total_ingreso }}">
                    </td>
                    <td>
                        <span>$ {{ $personal->iess_formatted }}</span>
                        <input type="hidden" name="iess[]" value="{{ $personal->iess }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->atrasos_faltas_formatted }}</span>
                        <input type="hidden" name="atrasos_faltas[]" value="{{ $personal->atrasos_faltas }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->anticipos_formatted }}</span>
                        <input type="hidden" name="anticipos[]" value="{{ $personal->anticipos }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->prestamo_iess_formatted }}</span>
                        <input type="hidden" name="prestamo_iess[]" value="{{ $personal->prestamo_iess }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->quincena_formatted }}</span>
                        <input type="hidden" name="quincena[]" value="{{ $personal->quincena }}">
                    </td>
                    <td class="edit-item" style="cursor: pointer;">
                        <span>$ {{ $personal->prestamo_jp_formatted }}</span>
                        <input type="hidden" name="prestamo_jp[]" value="{{ $personal->prestamo_jp }}">
                    </td>
                    <td>
                        <span>$ {{ $personal->total_descuentos_formatted }}</span>
                        <input type="hidden" name="total_descuentos[]" value="{{ $personal->total_descuentos }}">
                    </td>
                    <td>
                        <span>$ {{ $personal->total_recibir_formatted }}</span>
                        <input type="hidden" name="total_recibir[]" value="{{ $personal->total_recibir }}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-dark btn-remove-personal"> - </button>
                    </td>
                </tr>
            @endforeach

            <tr id="tr-default" style="display: {{ $ultimosDetalles->isEmpty() ? '' : 'none' }}">
                <td colspan="17" class="text-center">No existen elementos en la lista...</td>
            </tr>
        </tbody>
    </table>
</div>

@section('scripts')
    <script>
        var manoObraUrl = "{{ route('jp.limpieza.mano.obra.create', $proyecto->id) }}"
    </script>
    <script src="{{ asset('js/jp_limpieza/mano_obra.js?v=' . config('app.version', '')) }}"></script>
@endsection
