import { getFormData } from '../helpers.js';
$(function () {

    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#generar-reporte-adquisicion').click(function () {
        let data = getFormData($('#form-reporte'));
        if (!data.tipo_reporte) {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo de reporte.',
                'error'
            );
            return;
        }

        $.ajax({
            url: 'visualizar-reporte-adquisiciones',
            type: 'POST',
            data: data,
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $('#table-view-reporte').html(response.html);

                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    /** Cambiar tipo de adquisicion 
     * Al cambiar el select, actualizar el campo proveedores
    */
    $('select[name="tipo"]').on('change', function () {
        let tipoText = $('select[name=tipo] option:selected').text();
        $('select[name=proveedor]').empty();
        $('select[name=producto]').empty();

        if (tipoText != '') {
            tipoText = tipoText.replace(/\s+/g, "_").toLowerCase();
            $.ajax({
                url: 'filtro-reporte-adquisiciones',
                headers: { 'X-CSRF-TOKEN': csrf },
                type: 'GET',
                data: { tipo: tipoText },
                beforeSend: function () {
                    $('#loading').addClass('show');
                },
                success: function (response) {
                    $('select[name=proveedor]').append('<option value=""></option>');
                    $.each(response.result.proveedores, function (index, proveedor) {
                        $('select[name=proveedor]').append(
                            '<option value="' + proveedor.id + '">' + proveedor.nombre_proveedor + '</option>'
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

                    habilitarCampos();

                    $('[data-toggle="tooltip"]').tooltip();
                    $('[data-toggle="popover"]').popover({ html: true });
                },
                complete: function () {
                    $('#loading').removeClass('show');
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