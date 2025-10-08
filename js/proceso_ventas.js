import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#documento-identidad').on('input', function () {
        let valor = $(this).val();

        if (valor.length >= 10) {
            $.ajax({
                url: base_url + '/marketing/seguimiento-ventas/verificar-documento-identidad',
                headers: { 'X-CSRF-TOKEN': csrf },
                method: 'POST',
                data: { 'valor': valor },
                beforeSend: function () {
                    $('#loading').addClass('show');
                    $('.input_errors').remove();
                },
                success: function (response) {
                    if (response.success) {
                        var cliente = response.cliente;
                        $('input[name=nombre]').val(cliente.nombre);
                        $('input[name=telefono]').val(cliente.telefono);
                        $('input[name=email]').val(cliente.email);
                        $('input[name=direccion]').val(cliente.direccion);
                        $('input[name=observaciones]').val(cliente.observaciones);

                        Toast.fire({
                            icon: 'success',
                            text: 'Se cargaron datos de cliente existente.',
                        });

                    }

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
        }
    });

    $('#guardar-info-reserva').on('click', function () {
        var form = $("#form_reserva");
        var formData = getFormData(form);
        var procesoVentaId = $('input[name=proceso_venta_id]').val();
        var url = '/marketing/seguimiento-ventas/etapa-reserva';
        var method = 'POST';

        if (procesoVentaId != undefined) {
            url = '/marketing/seguimiento-ventas/actualizar-etapa-reserva/' + procesoVentaId;
            method = 'PUT';
        }


        $.ajax({
            url: base_url + url,
            headers: { 'X-CSRF-TOKEN': csrf },
            method: method,
            data: formData,
            beforeSend: function () {
                $('#loading').addClass('show');
                $('.input_errors').remove();
            },
            success: function (response) {

                if (response.success) {
                    // Mostrar mensaje de éxito
                    Toast.fire({
                        icon: 'success',
                        text: response.message,
                    });
                    form[0].reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ups.',
                        text: response.message,
                        confirmButtonText: 'Aceptar'
                    });
                }

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