<div class="form-row">
    <label class="col-12 col-form-label">Teléfono</label>
    @if ($proveedor->telefono)
        @foreach (explode_param($proveedor->telefono) as $telefono)
            <div class="form-group col-md-4">
                {{ Form::text('telefono[]', old('telefono[]', $telefono), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 1', 'maxlength' => '10']) }}
            </div>
        @endforeach
    @else
        <div class="form-group col-md-4">
            {{ Form::text('telefono[]', old('telefono[]'), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 1', 'maxlength' => '10']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::text('telefono[]', old('telefono[]'), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 2', 'maxlength' => '10']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::text('telefono[]', old('telefono[]'), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 3', 'maxlength' => '10']) }}
        </div>
    @endif

</div>
