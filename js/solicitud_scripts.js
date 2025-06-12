$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('select[name=tipo_solicitud]').on('change', function () {
        let selectedOption = $(this).find('option:selected').text().trim();
        if (selectedOption.toLowerCase() === 'eventualidad') {
            $("#disabled")
                .find("input, select") // Selecciona los elementos interactivos
                .prop("disabled", true);
            $('#check_recuperable').attr('disabled', true);
        } else {
            $("#disabled")
                .find("input, select") // Selecciona los elementos interactivos
                .prop("disabled", false);
            $('#check_recuperable').attr('disabled', false);
        }
    });

    $('input[name=fecha_desde], input[name=fecha_hasta]').datepicker().on('changeDate', function () {
        console.log('Fecha cambiada');
        calcularYMostrarTiempoLaboral();
    });

    // Escuchar cambios en los campos de hora usando el evento 'changeTime' del plugin Timepicker
    $('#hora_inicio, #hora_fin').on('change', function () {
        console.log('entro ' + $(this).val())
        calcularYMostrarTiempoLaboral();
    });

    /**
     * Buscar solcitud
     * @param String
     * return JSON
    */
    $('input:text[name=permisos_search]').on('keyup', function () {
        var $value = $(this).val();
        let tipo = $(this).attr('id');

        $.ajax({
            url: 'permisos/buscar',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'text': $value, 'tipo': tipo },
            beforeSend: function () {
            },
            success: function (data) {
                $('tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    // Forzar la actualización del Timepicker con el valor inicial del campo
    $('.timepicker').each(function () {
        let initialValue = $(this).val(); // Obtener el valor inicial del campo
        if (initialValue) {
            $(this).timepicker('setTime', initialValue); // Establecer el valor en el Timepicker
        }
    });


    $(document).on('click', '.eliminar-solicitud', function () {
        let id = $(this).attr('id');

        Swal.fire({
            title: '¿Esta Seguro?',
            text: "Una vez se elimina el registro no podrá recuperarlo.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, deseo Eliminarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'permisos/eliminar-solicitud/' + id,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (data) {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message,
                            });

                            $("#" + id).remove();

                            if ($('tbody').children().length == 0) {
                                $('tbody').html('<tr>' +
                                    '<td colspan = "7" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
                                    '</tr>');
                            }
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message,
                                'error'
                            )
                        }

                    })
                    .fail(function () {
                        Swal.fire(
                            'Error Inesperado!',
                            'No se pudo realizar la acción de eliminado, comuníquese con el administrador del sistema.',
                            'error'
                        )
                    });
            }
        });
    });

    // Función para calcular y mostrar el tiempo laboral
    function calcularYMostrarTiempoLaboral() {
        let fechaDesde = $('input[name=fecha_desde]').val(); // d-m-Y
        let horaDesde = $('input[name=hora_inicio]').val();  // H:i
        let fechaHasta = $('input[name=fecha_hasta]').val(); // d-m-Y
        let horaHasta = $('input[name=hora_fin]').val();     // H:i

        // Validar que todos los campos tengan valores
        if (!fechaDesde || !horaDesde || !fechaHasta || !horaHasta) {
            $('#resultado').text('00:00');
            return;
        }

        // Calcular el tiempo laboral
        let tiempoLaboral = calcularTiempoLaboral(fechaDesde, horaDesde, fechaHasta, horaHasta);

        // Mostrar el resultado
        $('#resultado').text(tiempoLaboral);
    }


});