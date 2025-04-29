<div class="col-md-2 ">
    <div class="select_wrapper">
        <label class="rounded  text-white">
            <input type="checkbox" name="orden_completa" class="d-none" value="true"
                {{ old('orden_completa') == true || (isset($orden_pedido->orden_recepcion->completado) && $orden_pedido->orden_recepcion->completado == true) || (isset($adquisicion) && $adquisicion->estado == 'completado') ? 'checked' : '' }}>
            <span class="text-center d-block py-3">Pedido Completo</span>
        </label>
    </div>
</div>
