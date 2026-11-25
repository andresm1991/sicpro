import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    var estadoActual = 0;

    // Los comentarios ya son HTML sanitizado en el servidor (S7): se
    // interpolan en crudo dentro de un div. escapeHtml solo se conserva para
    // el nombre del usuario.
    function escapeHtml(value) {
        return $('<div></div>').text(value === null || value === undefined ? '' : value).html();
    }

    // La descripcion de la agenda vive en un editor Summernote: .val() ya no
    // lee ni escribe su contenido y se usa la API ('code'). Los selectores
    // van acotados al modal/formulario porque 'descripcion' existe en mas de
    // un formulario de la pagina. Si el editor aun no esta inicializado se
    // cae al textarea plano.
    function summernoteCode($field) {
        if ($field.length && $field.next('.note-editor').length) {
            return $field.summernote('code');
        }

        return $field.val();
    }

    function setSummernoteCode($field, html) {
        if ($field.length && $field.next('.note-editor').length) {
            $field.summernote('code', html || '');
        } else {
            $field.val(html || '');
        }
    }

    // Editor inline para un comentario: mismas opciones de agenda que los
    // textareas fijos (altura 200, placeholder de comentario).
    function initComentarioEditor($field) {
        $field.summernote({
            placeholder: $field.data('placeholder') || 'Añadir un comentario...',
            tabsize: 2,
            height: $field.data('height') || 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }

    function destroyComentarioEditor($field) {
        if ($field.length && $field.next('.note-editor').length) {
            $field.summernote('destroy');
        }
    }

    // Solo puede haber un comentario en edicion: restaura cualquier otro
    // para no filtrar su HTML al nuevo editor.
    function restoreInlineEditors($exceptItem) {
        $('.list-comentarios .tarea-item').each(function () {
            const $other = $(this);
            if ($exceptItem && $other.is($exceptItem)) {
                return;
            }
            const $otherField = $other.find('textarea');
            if ($otherField.length) {
                const originalHtml = $otherField.data('original');
                destroyComentarioEditor($otherField);
                const $comentario = $('<div class="mb-1 comentario"></div>').html(originalHtml);
                $otherField.replaceWith($comentario);
                $other.find('.editar').show();
                $other.find('.eliminar').show();
                $other.find('.separator').show();
                $other.find('.d-flex.gap-2').remove();
            }
        });
    }

    $('#guardar').on('click', function () {
        var form = $("#form_tarea");
        var data = getFormData(form);

        // El textarea queda oculto tras el editor: se lee con la API.
        data.descripcion = summernoteCode(form.find('textarea[name=descripcion]'));

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

        // El título está en el encabezado del modal, fuera del formulario: se envía en
        // un único campo oculto que se reemplaza en cada guardado. La descripción no
        // necesita campo oculto porque el textarea ya forma parte del formulario.
        form.find('input[type=hidden][name=titulo]').remove();
        $('<input>', { type: 'hidden', name: 'titulo' })
            .val($('#titleComentarioModal').val())
            .appendTo(form);

        var data = getFormData(form);

        // Los textarea quedan ocultos tras el editor: se leen con la API.
        data.descripcion = summernoteCode(form.find('textarea[name=descripcion]'));
        data.comentario = summernoteCode(form.find('textarea[name=comentario]'));

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
                        setSummernoteCode(form.find('textarea[name=comentario]'), '');
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

        // El editor Summernote se alimenta con su API ('code'); .val() ya no
        // aplica. data-descripcion llega escapado en el atributo y jQuery lo
        // decodifica al leerlo.
        setSummernoteCode($('#comentarioTareaModal textarea[name=descripcion]'), descripcion);


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
        restoreInlineEditors($item);
        // El comentario ya es HTML sanitizado: se lee con .html() para
        // conservar el formato en el editor.
        const comentarioOriginal = $item.find('.comentario').html(); // Guardar el comentario original

        // Convertir el <div> en un <textarea> con las opciones de agenda
        const $textarea = $('<textarea class="form-control mb-1" data-height="200" data-placeholder="Añadir un comentario..."></textarea>');
        $textarea.data('original', comentarioOriginal);
        $item.find('.comentario').replaceWith($textarea);
        $textarea.val(comentarioOriginal);
        initComentarioEditor($textarea);

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
        const $field = $item.find('textarea');
        const nuevoComentario = summernoteCode($field); // Leer el HTML del editor

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
                // Destruir el editor para no filtrar su HTML a otra fila. Se
                // renderiza el HTML que devuelve el servidor, sanitizado: el
                // valor local del editor (codeview incluido) no paso por la
                // allowlist y no debe inyectarse.
                destroyComentarioEditor($field);
                const $comentario = $('<div class="mb-1 comentario"></div>')
                    .html(response.comentario || '');
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
        const $field = $item.find('textarea');
        const comentarioOriginal = $field.data('original'); // Recuperar el comentario original

        // Destruir el editor y volver al HTML original en crudo.
        destroyComentarioEditor($field);
        const $comentario = $('<div class="mb-1 comentario"></div>').html(comentarioOriginal);
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

    $(document).on('click', '.eliminar-tarea', function (e) {
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

        // Al cerrar se vacia el editor: la proxima tarea no debe heredar el
        // HTML de la anterior.
        setSummernoteCode($('#comentarioTareaModal textarea[name=descripcion]'), '');
    });

    $(document).on('hidden.bs.modal', '#tareasModal', function () {
        // Nueva tarea: no heredar el HTML del editor al reabrir el modal.
        setSummernoteCode($('#tareasModal textarea[name=descripcion]'), '');
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
                                                <h6 class="mb-1">${escapeHtml(tarea.usuario.nombre)}</h6>
                                                <small class="text-muted">${tarea.updated_at_formateado}</small>
                                            </div>
                                            <div class="mb-1 comentario">${tarea.comentario}</div>
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
    // Nota: el auto-ajuste de alto de #descripcion y su ResizeObserver se
    // eliminaron: peleaban con el editor Summernote, que gestiona su altura.

    // Función para verificar si la URL tiene un número al final
    function hasIdInUrl() {
        const path = window.location.pathname;
        return /\d+$/.test(path); // Verifica si termina con números
    }
});