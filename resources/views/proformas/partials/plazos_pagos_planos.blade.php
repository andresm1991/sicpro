<div class="form-group">
    <label class="col-form-label">PLAZO DE EJECUCION DEL PROYECTO</label>
    {{ Form::textarea(
        'plazo_ejecucion',
        $proforma->plazo_ejecucion ??
            "El proyecto se realizará mediante dos fases: \n- FASE UNO (14 días laborales): Se entregará planos arquitectónicos con su respectiva distribución, medidas de cada espacio.\n- FASE DOS (14 días laborales): Una vez aprobada la distribución, se procederá a elaborar el diseño en 3D del proyecto.\n- FASE TRES (14 días laborales): Una vez aprobada la FASE UNO y la FASE DOS, se procederá a realizar los planos arquitectónicos finales.\nEl plazo indicado será considerado a partir del día siguiente del depósito y entrega de los requerimientos acordados.",
        ['class' => 'form-control auto-resize', 'rows' => 5, 'placeholder' => 'Ingrese una nota para la proforma'],
    ) }}
</div>

<!-- SECCIÓN FORMA DE PAGO (EDITABLE) -->
<div class="row mt-5">
    <div class="col-md-8">
        <label class="col-form-label" style="margin-top:0;">FORMA DE PAGO:</label>
    </div>
    <div class="col-md-4">
        <label class="col-form-label text-center" style="margin-top:0;">ABONOS %</label>
    </div>
</div>
<div id="pagos-list">
    @isset($proforma->forma_pago)
        <!-- Items iniciales de pago -->
        @foreach ($proforma->forma_pago as $forma_pago)
            <div class="row mb-2 align-items-center">
                <div class="col-md-7">
                    {{ Form::text('forma_pago[]', $forma_pago, ['class' => 'form-control']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::text('abono[]', $proforma->abono[$loop->index] ?? '0', ['class' => 'form-control text-end input-enteros']) }}
                </div>
                <div class="col-md-2 text-end">
                    <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-pago-btn"><i
                            class="fas fa-trash"></i></a>
                </div>
            </div>
        @endforeach
    @else
        <div class="row mb-2 align-items-center">
            <div class="col-md-7">
                {{ Form::text('forma_pago[]', 'A la aprobación de la proforma', ['class' => 'form-control']) }}
            </div>
            <div class="col-md-3">
                {{ Form::text('abono[]', '50', ['class' => 'form-control text-end input-enteros']) }}
            </div>
            <div class="col-md-2 text-end">
                <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-pago-btn"><i class="fas fa-trash"></i></a>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <div class="col-md-7">
                {{ Form::text('forma_pago[]', 'A la firma de documentos para la aprobación de planos', ['class' => 'form-control']) }}
            </div>
            <div class="col-md-3">
                {{ Form::text('abono[]', '25', ['class' => 'form-control text-end input-enteros']) }}
            </div>
            <div class="col-md-2 text-end">
                <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-pago-btn"><i class="fas fa-trash"></i></a>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <div class="col-md-7">
                {{ Form::text('forma_pago[]', 'A la entrega del diseño aprobado con sus respectivos permisos de construcción.', ['class' => 'form-control']) }}
            </div>
            <div class="col-md-3">
                {{ Form::text('abono[]', '25', ['class' => 'form-control text-end input-enteros']) }}
            </div>
            <div class="col-md-2 text-end">
                <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-pago-btn"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    @endisset

</div>
<a href="javascript:void(0);" class="btn btn-dark btn-sm mt-2 form-group" id="add-pago-btn">+ Agregar Forma de Pago</a>
