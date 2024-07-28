<div class="form-group row">
    <label for="inputTelefono" class="col-sm-2 col-form-label">Teléfono</label>
    @if ($proveedor->telefono)
        @foreach (explode_param($proveedor->telefono) as $telefono)
            <div class="col">
                {{ Form::text('telefono[]', old('telefono[]', $telefono), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 1', 'maxlength' => '10']) }}
            </div>
        @endforeach
    @else
        <div class="col">
            {{ Form::text('telefono[]', old('telefono[]'), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 1', 'maxlength' => '10']) }}
        </div>
        <div class="col">
            {{ Form::text('telefono[]', old('telefono[]'), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 2', 'maxlength' => '10']) }}
        </div>
        <div class="col">
            {{ Form::text('telefono[]', old('telefono[]'), ['class' => 'form-control solo-numeros', 'placeholder' => 'Ingrese teléfono 3', 'maxlength' => '10']) }}
        </div>
    @endif

</div>
