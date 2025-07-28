<div class="form-group">
    <label class="col-form-label">Incluye</label>
    <div id="incluye-list">
        @isset($proforma->incluye)
            @foreach ($proforma->incluye as $incluye)
                <div class="list-item-input mb-2">
                    <div class="row">
                        <div class="col-md-10">
                            {{ Form::text('incluye[]', $incluye, ['class' => 'form-control flex-grow-1']) }}
                        </div>
                        <div class="col-md-2 d-flex  align-items-center">
                            <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-item-btn"><i
                                    class="fas fa-trash"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="list-item-input mb-2">
                <div class="row">
                    <div class="col-md-10">
                        {{ Form::text('incluye[]', 'Planos arquitectónicos', ['class' => 'form-control flex-grow-1']) }}
                    </div>
                    <div class="col-md-2 d-flex  align-items-center">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-item-btn"><i
                                class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>
            <div class="list-item-input mb-2">
                <div class="row">
                    <div class="col-md-10">
                        {{ Form::text('incluye[]', 'Planos estructurales.', ['class' => 'form-control flex-grow-1']) }}
                    </div>
                    <div class="col-md-2 d-flex  align-items-center">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-item-btn"><i
                                class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>

            <div class="list-item-input mb-2">
                <div class="row">
                    <div class="col-md-10">
                        {{ Form::text('incluye[]', 'Planos eléctricos', ['class' => 'form-control flex-grow-1']) }}
                    </div>
                    <div class="col-md-2 d-flex  align-items-center">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-item-btn"><i
                                class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>
            <div class="list-item-input mb-2">
                <div class="row">
                    <div class="col-md-10">
                        {{ Form::text('incluye[]', 'Planos hidrosanitarios.', ['class' => 'form-control flex-grow-1']) }}
                    </div>
                    <div class="col-md-2 d-flex  align-items-center">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-item-btn"><i
                                class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>

            <div class="list-item-input mb-2">
                <div class="row">
                    <div class="col-md-10">
                        {{ Form::text('incluye[]', 'Fotografías estáticas de la propuesta (1-5 RENDERS)', ['class' => 'form-control flex-grow-1']) }}
                    </div>
                    <div class="col-md-2 d-flex  align-items-center">
                        <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-item-btn"><i
                                class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>
        @endisset

    </div>
    <a href="javascript:void(0);" class="btn btn-dark btn-sm mt-2" id="add-incluye-btn">+ Agregar Ítem</a>

</div>
