import { getFormData } from './helpers.js';


$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    // Ocultar contener del años 
    // ya que solo se debe mostrar cuando el tipo de reporte es balance_global
    $('#contenedor-anios').hide();

    /**
         * Generar reporte balance gerencial
         */
    $('#generar-reporte-balance-gerencial').on('click', function () {
        const formaData = getFormData($('#form-reporte-balance-gerencial'));
        if (formaData.tipo_reporte == '') {
            Toast.fire({
                icon: 'warning',
                title: 'Seleccione el tipo de reporte para continuar.',
            });
            return;
        }

        $.ajax({
            url: base_url + '/gerencia/reporte/generar-reporte-balance',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: formaData,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                const data = response;
                $('#table-view-reporte').html(data);
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
    });

    /**
     * Generar el pdf del reporte
     */
    $(".generar-reporte").on('click', function () {
        const $form = $('#form-reporte-balance-gerencial');
        const formaData = getFormData($form);
        var tipo = $(this).data('action');
        var url = '';

        if (formaData.tipo_reporte == '') {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo de reporte.',
                'info'
            )
            return false;
        }
        url = base_url + '/gerencia/reporte/exportar/' + formaData.tipo_reporte;
        // Agregar el valor de tipo_reporte como parámetro en la URL
        $form.attr('action', `${url}`);
        $form.attr('target', '_blank');

        // Enviar el formulario
        $form.submit();
    });


    $('#select-proyecto').on('change', function () {
        var proyectoId = $(this).val();
        // Limpiar el select de subproyectos
        $('select[name=subproyecto]').empty();
        if (proyectoId <= 0) {
            $(".contenedor-nro-proforma").html('');
            $(".contenedor-nro-proforma").removeClass('col-md-4 col-12');
            return;
        }
        $.ajax({
            url: base_url + '/administrativo/subproyectos-por-proyecto',
            headers: { 'X-CSRF-TOKEN': csrf },
            method: 'POST',
            data: { 'proyecto_id': proyectoId },
            beforeSend: function () {
                $('#loading').addClass('show');
                $('.input_errors').remove();
            },
            success: function (response) {
                var subproyectos = response.subproyectos;
                var subproyectoSelect = $('select[name=subproyecto]');
                $.each(subproyectos, function (id, nombre) {
                    subproyectoSelect.append($('<option>', { value: id, text: nombre }));
                });
                // Inicializar Select2 después de agregar las opciones
                subproyectoSelect.trigger('change');
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
                            'error',
                        );
                }
            }
        });
    });

    $('select[name=tipo_reporte').on('change', function () {
        var valorSeleccionado = $(this).val();
        if (valorSeleccionado == 'balance_global') {
            $('select[name=proyecto]').prop('disabled', true);
            $('select[name=subproyecto]').prop('disabled', true);
            $('select[name=proyecto]').val(null).trigger('change');
            $('select[name=subproyecto]').val(null).trigger('change');
            $('#contenedor-fechas').hide();
            $('#contenedor-anios').show();


        } else {
            $('select[name=proyecto]').prop('disabled', false);
            $('select[name=subproyecto]').prop('disabled', false);
            $('#contenedor-anios').hide();
            $('#contenedor-fechas').show();
        }
    });


});