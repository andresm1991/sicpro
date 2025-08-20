<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <h4>terminos y condiciones</h4>
        </div>
    </div>

    <div class="col-sm-12 form-group">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <h6 class="mt-2">Validez de la proforma: </h6>
            </div>
            <div class="col-auto">
                {{ Form::text('validez', old('validez', $proforma->validez ?? '15 días calendario'), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>

    <div class="col-sm-12 form-group">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <h6 class="mt-2">forma de pago: </h6>
            </div>
            <div class="col-auto">
                {{ Form::text('forma_pago', old('forma_pago', $proforma->forma_pago ?? 'Contado'), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>

    <div class="col-sm-12 form-group">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <h6 class="mt-2">Tiempo de entrega: </h6>
            </div>
            <div class="col-auto">
                {{ Form::text('plazo_entrega', old('plazo_entrega', $proforma->plazo_entrega ?? '15 días laborables'), ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
</div>
