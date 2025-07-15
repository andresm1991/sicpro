<div class="row">
    <div class="col-sm-4 col-12">
        <label class="col-form-label">Tipo Plantilla</label>
        {{ Form::select('tipo_plantilla', ['JPLIMPIEZA' => 'JP Limpieza', 'ASOCIACION' => 'Asociación'], old('tipo_plantilla', $planificacion->tipo), ['class' => 'form-control select2-basic-single', 'id' => 'tipo_plantilla', 'data-placeholder' => 'Selecione opción']) }}
    </div>
    <div class="col-sm-4 col-12">
        <label class="col-form-label">Fecha desde</label>
        {{ Form::text('fecha_desde', old('fecha_desde', $planificacion->fecha_desde_formatted), ['class' => 'form-control datepicker', 'id' => 'fecha_desde', 'placeholder' => 'seleccione fecha']) }}
    </div>
    <div class="col-sm-4 col-12">
        <label class="col-form-label">Fecha hasta</label>
        {{ Form::text('fecha_hasta', old('fecha_hasta', $planificacion->fecha_hasta_formatted), ['class' => 'form-control datepicker', 'id' => 'fecha_hasta', 'placeholder' => 'seleccione fecha']) }}
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            <legend class="custom-legend"><span>Agregar Personal</span></legend>
            <fieldset class="custom-fieldset">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'Personal', ['class' => 'col-form-label']) }}
                            {{ Form::select('', getProveedores(true, 'mano.obra'), '', ['class' => 'form-control select2-basic-single', 'id' => 'proveedor', 'data-placeholder' => 'Selecione personal']) }}

                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'sueldo', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'sueldo', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'horas extras', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'horas_extras', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'total ganado', ['class' => 'col-form-label']) }}
                            {{ Form::label('', '$ 0.0000', ['class' => 'form-control label-disabled', 'id' => 'total_ganado']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'fondos', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'fondos', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'DECIMO TERCER SUELDO', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'decimo_tercero', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'DECIMO cuarto SUELDO', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'decimo_cuarto', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'total ingresos', ['class' => 'col-form-label']) }}
                            {{ Form::label('', '$ 0.0000', ['class' => 'form-control label-disabled', 'id' => 'total_ingresos']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'iess', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'iess', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>
                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'aporte patronal', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'aporte_patronal', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>
                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'atrasos y faltas', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'atrasos_faltas', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>
                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'anticipos', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'anticipos', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>
                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'prestamo iess', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'prestamo_iess', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'quincena', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'quincena', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>
                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'prestamo jp', ['class' => 'col-form-label']) }}
                            {{ Form::text('', '', ['class' => 'form-control moneyDosDecimales', 'id' => 'prestamo_jp', 'placeholder' => '$ 0.00']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'total descuentos', ['class' => 'col-form-label']) }}
                            {{ Form::label('', '$ 0.0000', ['class' => 'form-control label-disabled', 'id' => 'total_descuentos']) }}
                        </div>
                    </div>

                    <div class="col-sm-2 col-12">
                        <div class="form-group">
                            {{ Form::label('', 'total a recibir', ['class' => 'col-form-label']) }}
                            {{ Form::label('', '$ 0.0000', ['class' => 'form-control label-disabled', 'id' => 'total_recibir']) }}
                        </div>
                    </div>

                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-dark" id="agregar-personal">Agregar Personal</button>
                </div>
            </fieldset>
        </div>
    </div>

</div>
