import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    $('.generar-reporte').on('click', function () {
        const action = $(this).data('action'); // Obtener el valor de data-action

        // Actualizar el campo oculto con el tipo de reporte
        $('#tipo_reporte').val(action);

        // Obtener la acción base del formulario (sin modificaciones previas)
        let formAction = $('#form-reporte-adquisiciones').attr('action');

        // Eliminar cualquier sufijo previo agregado al action
        formAction = formAction.split('/pdf')[0].split('/excel')[0];
        // Agregar el valor de tipo_reporte como parámetro en la URL
        $('#form-reporte-adquisiciones').attr('action', `${formAction}/${action}`);

        // Enviar el formulario
        $('#form-reporte-adquisiciones').submit();
    });

    $('select[name=tipo]').on('change', function () {
        let tipoText = $('select[name=tipo] option:selected').text();
        $('select[name=proveedor]').empty();
        $('select[name=producto]').empty();
        $('select[name=cargo]').empty();

        if (tipoText != '') {
            tipoText = tipoText.replace(/\s+/g, "_").toLowerCase();
            $.ajax({
                url: 'datos-filtro-reporte-adquisiciones',
                headers: { 'X-CSRF-TOKEN': csrf },
                type: 'GET',
                data: { tipo: tipoText },
                beforeSend: function () {

                },
                success: function (response) {
                    console.log(response);
                    $('select[name=proveedor]').append('<option value=""></option>');
                    $.each(response.result.proveedores, function (index, proveedor) {
                        $('select[name=proveedor]').append(
                            '<option value="' + proveedor.id + '">' + proveedor.razon_social + '</option>'
                        );
                    });

                    $('select[name=producto]').append('<option value=""></option>');
                    $.each(response.result.productos, function (index, value) {
                        $('select[name=producto]').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
                    });

                    $('select[name=cargo]').append('<option value=""></option>');
                    $.each(response.result.cargos, function (index, value) {
                        $('select[name=cargo]').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
                    });
                    //$("#categoria_tarea").val(response.categoria).trigger('change');

                    $('[data-toggle="tooltip"]').tooltip();
                    $('[data-toggle="popover"]').popover({ html: true });
                },
                complete: function () {
                    $('#comentarioTareaModal #modal-overlay').hide();
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                switch (jqXHR.status) {
                    case 422: // ERROR INPUT VALIDATE

                        break;

                    case 419: // ERROR EXPIRATE SESSION
                        window.location = '/';
                        break;

                    default:
                        var errors = JSON.parse(jqXHR.responseText);
                        Swal.fire(
                            'Ups.!',
                            'Algo salió mal, por favor vuelva a intentarlo.',
                            'error'
                        )
                        console.log(errors)
                }
            });
        }
    });
});