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
                        //location.reload();
                        $('.tareas-items').html(response.html);
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
        form.append('<input type="hidden" name="titulo" value="' + $('input[name=titulo]').val() + '">');
        form.append('<input type="hidden" name="descripcion" value="' + $('#descripcion').val() + '">');

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
                        $("#comentario").val('');
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
        let colaboradores = $(this).data('colaboradores');
        estadoActual = $(this).data('estado');

        const $textarea = $('#descripcion');

        // Establecer el contenido del textarea antes de ajustar la altura
        $textarea.val(descripcion);
        // Ajustar la altura dinámicamente al escribir


        // Asignar valores a otros campos del modal
        $('#titleComentarioModal').val(titulo);
        $('input:hidden[name=tarea_id]').val(tarea);
        $('select[name=estado]').val(estadoActual).trigger('change');
        $('#list_usuarios').val(colaboradores).trigger('change');

        // Mostrar el modal
        $('#comentarioTareaModal').modal({
            backdrop: 'static', // No permite cerrar el modal al hacer clic fuera
            keyboard: false     // No permite cerrar el modal usando la tecla ESC
        }).modal('show');

        getComentariosTarea();
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

        const params = new URLSearchParams(window.location.search);
        const filtrarAgenda = params.get('filtrar_agenda');
        const filtrarUser = params.get('user');

        $.ajax({
            url: 'agenda/actualizar-estado-tarea/' + tarea_id,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'PUT',
            data: { estado: estado, 'filtrar_agenda': filtrarAgenda, 'filtrar_user': filtrarUser },
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
                        $('.tareas-items').html(response.html);
                        //location.reload();
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
                                //location.reload();
                                $('.tareas-items').html(response.html);
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

    $('#aplicar-filtro').on('click', function () {
        // Obtener la opción seleccionada
        const filtroValor = $('#filtro-agenda').val();
        const selectedOption = $('#filtro-agenda').find('option:selected');
        // Obtener el grupo (<optgroup>) al que pertenece la opción seleccionada
        const group = selectedOption.parent().attr('label');

        if (group != undefined) {
            if (group == 'colaborador') {
                window.location.href = `?filtrar_agenda=&user=${encodeURIComponent(filtroValor)}`;
            } else if (group == 'categoria') {
                window.location.href = `?filtrar_agenda=${encodeURIComponent(filtroValor)}`;
            }
        } else {
            window.location.href = `?filtrar_agenda=${encodeURIComponent(filtroValor)}`;
        }
    });

    $(document).on('hidden.bs.modal', '#comentarioTareaModal', function () {
        //window.location.href = url;

    });

    $(document).on('shown.bs.modal', '#tareasModal, #comentarioTareaModal, #filtroExportarTareasModal', function () {
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

        $(this).find('.select2-tag').each(function () {
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
                allowClear: false
            });

            // Guardar la nueva configuración
            $select.data('select2-config', newOptions);

            // Inicializar Select2 con la configuración fusionada
            $select.select2(newOptions);
        });

        // Ajustar la altura inicial del textarea al abrir el modal
        adjustTextareaHeight($('#descripcion'));
    });

    $(document).on('click', '#exportar-tarea', function () {
        var form = $("#form_filtro_tareas");
        var data = getFormData(form);
        const baseUrl = 'generar-pdf/exportar-tareas';
        const params = new URLSearchParams(data).toString(); // Convertir el objeto de datos a una cadena de consulta
        const url = `${baseUrl}?${params}`; // Construir la URL con los parámetros de consulta
        console.log(url)
        window.open(url, '_blank');
    });


    function getComentariosTarea() {
        $.ajax({
            url: url + '/comentarios-tarea',
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
                // Recopilar los IDs de los colaboradores
                let colaboradoresIds = response.colaboradores.map(colaborador => colaborador.usuario.id);
                // Asignar el array de IDs al select múltiple
                $("#list_usuarios").val(colaboradoresIds).trigger('change');

                $("#categoria_tarea").val(response.categoria).trigger('change');

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
    $('#descripcion').off('input').on('input', function () {
        adjustTextareaHeight($(this));
    });

    // Función para ajustar la altura del textarea
    function adjustTextareaHeight($textarea) {
        $textarea.css('height', 'auto'); // Restablece la altura
        $textarea.css('height', $textarea[0].scrollHeight + 'px'); // Ajusta la altura al contenido
    }

    // Función para verificar si la URL tiene un número al final
    function hasIdInUrl() {
        const path = window.location.pathname;
        return /\d+$/.test(path); // Verifica si termina con números
    }
});