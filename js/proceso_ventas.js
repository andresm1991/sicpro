import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#guardar-info-reserva').on('click', function () {
        var form = $("#form_reserva");
        var formData = getFormData(form);

        $.ajax({
            url: base_url + '/marketing/seguimiento-ventas/etapa-reserva',
            headers: { 'X-CSRF-TOKEN': csrf },
            method: 'POST',
            data: formData,
            beforeSend: function () {
                $('#loading').addClass('show');
                $('.input_errors').remove();
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
            error: function (jqXHR) {
                switch (jqXHR.status) {
                    case 422: // ERROR INPUT VALIDATE
                        $.each(jqXHR.responseJSON.errors, function (i, error) {
                            var el = $(document).find('[name="' + i + '"]');
                            el.after($('<small class="input_errors" style="color: red;">' + error[0] + '</small>'));
                        });
                        break;

                    case 419: // ERROR EXPIRATE SESSION
                        window.location = '/';
                        break;

                    default:
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


            }
        });
    });
})