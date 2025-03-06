import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    var estadoActual = 0;

    $('#guardar').on('click', function () {
        var form = $("#form_tarea");
        var data = getFormData(form);

        $.ajax({
            url: 'agenda/guardar-tarea',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#tareasModal #modal-overlay').show();
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
                $('#tareasModal #modal-overlay').hide();
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
                $('#comentarioTareaModal #modal-overlay').show();
            },
            success: function (response) {

                Swal.fire({
                    icon: response.success ? "success" : "error",
                    text: response.message,
                    confirmButtonText: 'Aceptar',
                }).then((result) => {
                    if (response.success) {
                        getComentariosTarea();
                    }
                });


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
    });

    $(document).on('click', '.agregar-comentario', function () {
        let titulo = $(this).data('titulo');
        let descripcion = $(this).data('descripcion');
        let tarea = $(this).attr('id');
        estadoActual = $(this).data('estado');

        $('#titleComentarioModal').text(titulo);
        $('#descripcion').text(descripcion);
        $('input:hidden[name=tarea_id]').val(tarea)
        $('select[name=estado]').val(estadoActual).trigger('change');

        getComentariosTarea();


        $('#comentarioTareaModal').modal({
            backdrop: 'static', // No permite cerrar el modal al hacer clic fuera
            keyboard: false     // No permite cerrar el modal usando la tecla ESC
        }).modal('show');
    });

    // Manejar el evento de clic en "Editar"
    $(document).on('click', '.list-comentarios .editar', function (e) {
        e.preventDefault();

        const $item = $(this).closest('.tarea-item'); // Obtener el contenedor de la tarea
        const comentarioOriginal = $item.find('.comentario').text(); // Guardar el comentario original

        // Convertir el <p> en un <textarea>
        const $textarea = $('<textarea class="form-control mb-1" data-original="' + comentarioOriginal + '"></textarea>').val(comentarioOriginal);
        $item.find('.comentario').replaceWith($textarea);

        // Agregar botones de "Aceptar" y "Cancelar"
        const $botones = $(
            '<div class="d-flex gap-2">' +
            '<button class="btn btn-sm btn-success aceptar mr-1"><i class="fas fa-check "></i></button>' +
            '<button class="btn btn-sm btn-danger cancelar"><i class="fas fa-times"></i></button>' +
            '</div>'
        );
        $(this).parent().append($botones);

        // Ocultar los botones originales ("Editar" y "Eliminar")
        $(this).hide();
        $item.find('.eliminar').hide();
        $item.find('.separator').hide(); // Ocultar el separador
    });

    // Manejar el evento de clic en "Eliminar"
    $(document).on('click', '.list-comentarios .eliminar', function (e) {
        e.preventDefault();

        const $item = $(this).closest('.tarea-item'); // Obtener el contenedor de la tarea
        const comentarioId = $item.data('id'); // Obtener el ID de la tarea

        // Confirmar la eliminación
        Swal.fire({
            title: '¿Estás seguro?',
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
                    url: `agenda/eliminar-comentario/${comentarioId}`,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (data) {
                        if (data.success) {
                            // Eliminar el elemento del DOM
                            $item.remove();

                        }

                        Toast.fire({
                            title: data.success ? 'Success!' : 'Error!',
                            text: data.message,
                            icon: data.success ? 'success' : 'error',

                        });

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

    // Manejar el evento de clic en "Aceptar"
    $(document).on('click', '.list-comentarios .aceptar', function (e) {
        e.preventDefault();

        const $item = $(this).closest('.tarea-item'); // Obtener el contenedor de la tarea
        const comentarioId = $item.data('id'); // Obtener el ID de la tarea
        const nuevoComentario = $item.find('textarea').val(); // Obtener el nuevo comentario

        // Enviar los datos actualizados mediante AJAX
        $.ajax({
            headers: { 'X-CSRF-TOKEN': csrf },
            url: `agenda/comentarios-tarea/${comentarioId}`, // Endpoint para actualizar el comentario de la tarea
            method: 'PUT',
            data: { comentario: nuevoComentario },
            beforeSend: function () {
                $('#comentarioTareaModal #modal-overlay').show();
            },
            success: function (response) {
                // Volver al estado original
                const $comentario = $('<p class="mb-1 comentario"></p>').text(nuevoComentario);
                $item.find('textarea').replaceWith($comentario);

                // Restaurar los botones originales
                $item.find('.editar').show();
                $item.find('.eliminar').show();
                $item.find('.separator').show();
                $item.find('.d-flex.gap-2').remove(); // Eliminar los botones temporales
            },
            complete: function () {
                $('#comentarioTareaModal #modal-overlay').hide();
            },
            error: function (error) {
                console.error('Error:', error);
                Swal.fire(
                    'Ups.!',
                    'Ocurrió un error al actualizar la tarea.',
                    'error'
                );
            }
        });
    });

    // Manejar el evento de clic en "Cancelar"
    $(document).on('click', '.list-comentarios .cancelar', function (e) {
        e.preventDefault();

        const $item = $(this).closest('.tarea-item'); // Obtener el contenedor de la tarea
        const comentarioOriginal = $item.find('textarea').data('original'); // Recuperar el comentario original

        // Volver al estado original
        const $comentario = $('<p class="mb-1 comentario"></p>').text(comentarioOriginal);
        $item.find('textarea').replaceWith($comentario);

        // Restaurar los botones originales
        $item.find('.editar').show();
        $item.find('.eliminar').show();
        $item.find('.separator').show();
        $item.find('.d-flex.gap-2').remove(); // Eliminar los botones temporales
    });

    $('#comentarioTareaModal').on('change', 'select[name=estado]', function () {
        let estado = $(this).val();
        let tarea_id = $('input:hidden[name=tarea_id]').val();

        if (estadoActual == estado) {
            return;
        }
        $.ajax({
            url: 'agenda/actualizar-estado-tarea/' + tarea_id,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'PUT',
            data: { estado: estado },
            beforeSend: function () {
                $('#comentarioTareaModal #modal-overlay').show();
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
    });

    $('.eliminar-tarea').on('click', function (e) {
        e.preventDefault(); // Prevenir el comportamiento predeterminado del botón
        e.stopPropagation(); // Detener la propagación del evento al elemento padre (<a>)

        const tareaId = $(this).attr('id'); // Obtener el ID de la tarea
        // Confirmar la eliminación
        Swal.fire({
            title: '¿Estás seguro?',
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
                    url: `agenda/eliminar-tarea/${tareaId}`,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (response) {
                        Swal.fire({
                            icon: response.success ? "success" : "error",
                            text: response.message,
                            confirmButtonText: 'Aceptar',
                        }).then((result) => {
                            if (response.success) {
                                location.reload();
                            }
                        });
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

    $(document).on('shown.bs.modal', '#tareasModal', function () {
        $(this).find('.select2-basic-single').each(function () {
            let $select = $(this);

            // Destruir Select2 si ya está inicializado
            if ($select.data('select2')) {
                $select.select2('destroy');
            }

            // Obtener la configuración original almacenada en `data()`
            let originalOptions = $select.data('select2-config') || {};

            // Extender las opciones sin perder `createTag` ni `insertTag`
            let newOptions = $.extend(true, {}, originalOptions, {
                dropdownParent: $select.closest('.modal'),
                placeholder: $select.data('placeholder') || 'Seleccione una opción',
                allowClear: true
            });

            // Guardar la nueva configuración
            $select.data('select2-config', newOptions);

            // Inicializar Select2 con la configuración fusionada
            $select.select2(newOptions);
        });
    });

    function getComentariosTarea() {
        $.ajax({
            url: 'agenda/comentarios-tarea',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { tarea_id: $('input:hidden[name=tarea_id]').val() },
            beforeSend: function () {
                $('#comentarioTareaModal #modal-overlay').show();
                $("#comentarios").html('');
            },
            success: function (response) {
                $.each(response.comentarios, function (index, tarea) {
                    $("#comentarios").append(`<div class="list-group list-comentarios">
                                        <div class="list-group-item list-group-item-action tarea-item" data-id="${tarea.id}">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">${tarea.usuario.nombre}</h6>
                                                <small class="text-muted">${tarea.updated_at_formateado}</small>
                                            </div>
                                            <p class="mb-1 comentario">${tarea.comentario}</p>
                                            <a href="#" class="text-dark font-weight-bold font-size-12 editar">Editar</a>  <span class="separator">|</span>  <a href="#" class="text-dark font-weight-bold font-size-12 eliminar">Eliminar</a>
                                        </div>
                                    </div>`);
                });

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