export const getFormData = ($form) => {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
};

export const limpiarFormulario = ($formularioId) => {
    // Resetear todos los inputs de tipo texto, número, email, etc.
    $($formularioId).find('input:not([type="button"], [type="submit"], [type="reset"])').val('');

    // Resetear todos los select2
    $($formularioId).find('select').each(function () {
        $(this).val(null).trigger('change'); // Limpia y actualiza el select2
    });

    // Resetear los textareas
    $($formularioId).find('textarea').val('');

    // Si hay checkboxes o radios, deseleccionarlos
    $($formularioId).find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
}