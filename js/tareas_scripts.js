import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    $('#guardar').on('click', function () {
        var form = $("#form_tarea");
        var data = getFormData(form);

        $.ajax({
            url: 'agenda/guardar-tarea',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#modal-overlay').show();
            },
            success: function (response) {

                Swal.fire({
                    icon: response.success ? "success" : "error",
                    text: response.message,
                    confirmButtonText: 'Aceptar',
                }).then((result) => {
                    if (response.success) {
                        location.reload();
                    }
                });


                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            complete: function () {
                $('#modal-overlay').hide();
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

    $('#guardar-comentario').on('click', function () {
        var form = $("#form_comentario_tarea");
        var data = getFormData(form);

        $.ajax({
            url: 'agenda/guardar-comentario',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#modal-overlay').show();
            },
            success: function (response) {

                Swal.fire({
                    icon: response.success ? "success" : "error",
                    text: response.message,
                    confirmButtonText: 'Aceptar',
                }).then((result) => {
                    if (response.success) {
                        location.reload();
                    }
                });


                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            complete: function () {
                $('#modal-overlay').hide();
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

    $(document).on('click', '.agregar-comentario', function () {
        let titulo = $(this).data('titulo');
        let descripcion = $(this).data('descripcion');
        let tarea = $(this).attr('id');

        $('#titleComentarioModal').text(titulo);
        $('#descripcion').text(descripcion);
        $('input:hidden[name=tarea_id]').val(tarea)

        $('#comentarioTareaModal').modal({
            backdrop: 'static', // No permite cerrar el modal al hacer clic fuera
            keyboard: false     // No permite cerrar el modal usando la tecla ESC
        }).modal('show');
    });
});