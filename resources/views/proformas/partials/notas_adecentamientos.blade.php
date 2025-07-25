<div class="form-group">
    <label class="col-form-label">Notas</label>
    {{ Form::textarea(
        'nota',
        old(
            'nota',
            $proforma->nota ??
                'Los trabajos incluyen todo el equipamiento de seguridad requerido, así como la seguridad social del personal que ingrese a trabajar.',
        ),
        ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Ingrese una nota para la proforma'],
    ) }}
</div>
