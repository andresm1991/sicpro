$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    // Obtén la URL completa
    let url = window.location.href;

    // Divide la URL por cada "/"
    let segments = url.split('/');

    // Obtén el último segmento
    let lastSegment = segments.pop() || segments.pop();  // Maneja caso de '/' al final

    /**
     * Filtrar adquisiciones
     */

    $('input:text[name=adquisicion_search]').on('keyup', function () {
        var $value = $(this).val();
        var $tipo = $(this).attr('id');
        
        $.ajax({
            url: url+'/buscar-adquisicion',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value , 'tipo': $tipo},
            beforeSend: function () {
            },
            success: function (data) {
                $('#table-list-pedidos-'+$tipo+' tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX:", error);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    $(document).on('change', '.pagado', function () {
        const $checkbox = $(this); // Almacenar el elemento actual
        var id_pago = $(this).val();

        if ($checkbox.is(':checked')) {
            message().then((resultado) => {
                if (resultado) {
                    $.ajax({
                        url:'/pago_orden_trabajo/'+id_pago,
                        headers: { 'X-CSRF-TOKEN': csrf },
                        type: 'PUT',
                        data: {'id_pago':id_pago},
                        dataType: 'json',
                    })
                    .done(function (data) {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message,
                            });

                            $checkbox.attr('disabled', true)
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message,
                                'error'
                            )
                            $checkbox.prop('checked', false); // Desmarcar el checkbox
                        }

                    })
                    .fail(function () {
                        Swal.fire(
                            'Error Inesperado!',
                            'No se pudo realizar la acción solicitada, comuníquese con el administrador del sistema.',
                            'error'
                        )
                        $checkbox.prop('checked', false); // Desmarcar el checkbox
                    });
                } else {
                    $checkbox.prop('checked', false); // Desmarcar el checkbox
                }
            }).catch((error) => {
                console.error('Ocurrió un error:', error);
                $checkbox.prop('checked', false); // Desmarcar el checkbox
            });
        }
    });
});