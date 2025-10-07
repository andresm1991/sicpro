import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#guardar-info-reserva').on('click', function () {
        var form = $("#form_reserva");
        var formData = getFormData(form);

        $.ajax({
            url: base_url + '/marketing/seguimiento-ventas',
            headers: { 'X-CSRF-TOKEN': csrf },
            method: 'POST',
            data: formData,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {

                console.log(response);
                // Mostrar mensaje de éxito
                Toast.fire({
                    icon: 'success',
                    text: `Información guardada con éxito`,
                });
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function (xhr) {
                // Manejar errores (por ej. de validación)
                let errorMsg = 'Ocurrió un error inesperado.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire(
                    'Error!',
                    errorMsg,
                    'error'
                );
            }
        });
    });
})