
$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var listId = '';

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
                                'error',
                            );
                    }
                }
            });
        }
    });

    $('#guardar-info-reserva').on('click', function () {
        var form = $("#form_reserva");
        var formData = new FormData(form[0]);
        var procesoVentaId = $('input[name=proceso_venta_id]').val();
        var url = '/marketing/seguimiento-ventas/etapa-reserva';

        if (procesoVentaId != undefined) {
            url = '/marketing/seguimiento-ventas/actualizar-etapa-reserva/' + procesoVentaId;
            formData.append('_method', 'PUT'); // Añadimos el campo _method para decirle a Laravel que es un PUT
        }


        $.ajax({
            url: base_url + url,
            headers: { 'X-CSRF-TOKEN': csrf },
            method: 'POST',
            data: formData,
            processData: false, // Evita que jQuery procese los datos
            contentType: false, // Evita que jQuery establezca el Content-Type
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
                    $('select[name=proyecto]').val(null).trigger('change');

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

    // Manejador para el formulario de DOCUMENTACIÓN
    $('#form_agregar_documento').on('submit', function (e) {
        e.preventDefault(); // ¡MUY IMPORTANTE! Evita que la página se recargue

        let form = $(this);
        let url = form.attr('action');
        let nombreInput = $('#nombre-documento-input');
        let nombre = nombreInput.val().trim();

        if (nombre === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Campo vacío',
                text: 'Por favor, ingresa el nombre del documento.'
            });
            return;
        }

        $.ajax({
            url: url,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: "POST",
            data: {
                nombre: nombre
            },
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                if (response.success) {
                    // Limpia el mensaje de "lista vacía" si existe
                    $('#empty-doc-item').remove();

                    const item = response.item;
                    const updateUrl = `/documentacion-items/${item.id}`; // Construimos la URL
                    const deleteUrl = `/documentacion-items/${item.id}`;

                    const estadoBadge = item.estado === 'pendiente' ?
                        '<span class="badge badge-warning item-estado">pendiente</span>' :
                        item.estado === 'entregado' ?
                            '<span class="badge badge-info item-estado">entregado</span>' :
                            item.estado === 'en_revision' ?
                                '<span class="badge badge-secondary item-estado">en revisión</span>' :
                                item.estado === 'aprobado' ? '<span class="badge badge-success item-estado">aprobado</span>' :
                                    '<span class="badge badge-secondary item-estado">desconocido</span>';

                    const newItemHtml = `
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="doc-item-${item.id}">
                           <div class="col-sm-8 col-12">
                               <span class="item-nombre">${item.nombre}</span>
                               <small class="text-muted d-block item-observaciones">${item.observaciones ?? ''}</small>
                           </div>
                           <div class="col-sm-2 col-12 item-estado">
                               ${estadoBadge}
                           </div>
                           <div class="col-sm-2 col-12 d-flex justify-content-end">
                               <button class="btn btn-sm btn-secondary edit-item-btn" 
                                       data-bs-toggle="modal" data-bs-target="#editItemModal"
                                       data-item-id="${item.id}" data-item-nombre="${item.nombre}"
                                       data-item-estado="${item.estado}" data-item-observaciones="${item.observaciones}"
                                       data-update-url="${updateUrl}">
                                   Editar
                               </button>
                               <button class="btn btn-sm btn-danger delete-item-btn" 
                                       data-item-id="${item.id}"
                                       data-delete-url="${deleteUrl}">
                                   Eliminar
                               </button>
                           </div>
                        </li>`;

                    $('#lista-documentacion').append(newItemHtml);
                    nombreInput.val('');

                    // Mostrar una notificación de éxito más elegante
                    Toast.fire({
                        icon: 'success',
                        text: response.message,
                    });
                }
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function (xhr) {
                // Manejo de errores (por ejemplo, validación fallida)
                let errorMsg = 'Ocurrió un error. Inténtalo de nuevo.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).join('\n');
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        });
    });


    // Manejador para el formulario de escrituracion
    $('#form_agregar_escrituracion').on('submit', function (e) {
        e.preventDefault(); // ¡MUY IMPORTANTE! Evita que la página se recargue

        let form = $(this);
        let url = form.attr('action');
        let nombreInput = $('#nombre-escituracion-input');
        let nombre = nombreInput.val().trim();

        if (nombre === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Campo vacío',
                text: 'Por favor, ingresa el nombre del documento.'
            });
            return;
        }

        $.ajax({
            url: url,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: "POST",
            data: {
                nombre: nombre
            },
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                if (response.success) {
                    // Limpia el mensaje de "lista vacía" si existe
                    $('#empty-escrituracion-item').remove();

                    const item = response.item;
                    const updateUrl = `/escrituracion-items/${item.id}`; // Construimos la URL
                    const deleteUrl = `/escrituracion-items/${item.id}`;

                    const estadoBadge = item.completado ?
                        '<span class="badge badge-success">completado</span>' : '<span class="badge badge-warning">Pendiente</span>';

                    const newItemHtml = `
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="doc-escrituracion-${item.id}">
                           <div class="col-sm-8 col-12">
                               <span class="item-nombre">${item.nombre}</span>
                               <small class="text-muted d-block item-observaciones">${item.observaciones ?? ''}</small>
                           </div>
                           <div class="col-sm-2 col-12 item-estado">
                               ${estadoBadge}
                           </div>
                           <div class="col-sm-2 col-12 d-flex justify-content-end">
                               <button class="btn btn-sm btn-secondary edit-item-btn" 
                                       data-bs-toggle="modal" data-bs-target="#editItemModal"
                                       data-item-id="${item.id}" data-item-nombre="${item.nombre}"
                                       data-item-estado="${item.estado}" data-item-observaciones="${item.observaciones}"
                                       data-update-url="${updateUrl}">
                                   Editar
                               </button>
                               <button class="btn btn-sm btn-danger delete-item-btn" 
                                       data-item-id="${item.id}"
                                       data-delete-url="${deleteUrl}">
                                   Eliminar
                               </button>
                           </div>
                        </li>`;

                    $('#lista-escrituracion').append(newItemHtml);
                    nombreInput.val('');

                    // Mostrar una notificación de éxito más elegante
                    Toast.fire({
                        icon: 'success',
                        text: response.message,
                    });
                }
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function (xhr) {
                // Manejo de errores (por ejemplo, validación fallida)
                let errorMsg = 'Ocurrió un error. Inténtalo de nuevo.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).join('\n');
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        });
    });

    $('#editItemModal').on('show.bs.modal', function (e) {
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
                allowClear: false
            });

            // Guardar la nueva configuración
            $select.data('select2-config', newOptions);

            // Inicializar Select2 con la configuración fusionada
            $select.select2(newOptions);
        });
    });

    // --- MANEJADOR PARA ABRIR EL MODAL DE EDICIÓN ---
    $(document).on('click', '#lista-documentacion .edit-item-btn, #lista-escrituracion .edit-item-btn', function () {
        let button = $(this);
        let nombre = button.data('item-nombre');
        let estado = button.data('item-estado');
        let observaciones = button.data('item-observaciones');
        let updateUrl = button.data('update-url');

        let listContainer = button.closest('ul');
        listId = listContainer.attr('id');

        let selectEstado = $('#modalItemEstado');
        selectEstado.html('');
        if (listId === 'lista-escrituracion') {
            // Opciones para la lista de Escrituración
            let opcionesEscrituracion = `
            <option value="0">Pendiente</option>
            <option value="1">Aprobado</option>
        `;
            selectEstado.html(opcionesEscrituracion);
        } else {
            // Opciones por defecto (para lista-documentacion y cualquier otro caso)
            let opcionesDocumentacion = `
            <option value="pendiente">Pendiente</option>
            <option value="entregado">Entregado</option>
            <option value="en_revision">En Revisión</option>
            <option value="aprobado">Aprobado</option>
        `;
            selectEstado.html(opcionesDocumentacion);
        }

        // Llenar el modal con los datos del item
        $('#modalItemNombre').val(nombre);
        selectEstado.val(estado).trigger('change');
        $('#modalItemObservaciones').val(observaciones);

        // Establecer la acción del formulario del modal
        $('#editItemForm').attr('action', updateUrl);
    });


    // --- MANEJADOR PARA ENVIAR EL FORMULARIO DEL MODAL ---
    $('#guardar-edit-item').on('click', function (e) {
        e.preventDefault();

        let form = $('#editItemForm');
        let url = form.attr('action');
        let data = form.serialize();


        $.ajax({
            type: "PATCH",
            url: url,
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                if (response.success) {
                    const item = response.item;
                    var listItem = '', estadoBadge = '';

                    if (listId === 'lista-escrituracion') {
                        listItem = $(`#doc-escrituracion-${item.id}`);

                        estadoBadge = item.completado ? '<span class="badge badge-success">Completado</span>' : '<span class="badge badge-warning">pendiente</span>';

                    } else {
                        listItem = $(`#doc-item-${item.id}`);

                        estadoBadge = item.estado === 'pendiente' ?
                            '<span class="badge badge-warning item-estado">pendiente</span>' :
                            item.estado === 'entregado' ?
                                '<span class="badge badge-info item-estado">entregado</span>' :
                                item.estado === 'en_revision' ?
                                    '<span class="badge badge-secondary item-estado">en revisión</span>' :
                                    item.estado === 'aprobado' ? '<span class="badge badge-success item-estado">aprobado</span>' :
                                        '<span class="badge badge-secondary item-estado">desconocido</span>';


                    }

                    // Actualizar la información en la lista principal
                    listItem.find('.item-estado').html(estadoBadge);
                    listItem.find('.item-observaciones').text(item.observaciones);

                    // Actualizar los data-attributes del botón de editar para futuras ediciones
                    const editBtn = listItem.find('.edit-item-btn');
                    editBtn.data('item-estado', item.estado);
                    editBtn.data('item-observaciones', item.observaciones);


                    // Cerrar el modal
                    $('#editItemModal').modal('hide');

                    // Mostrar notificación de éxito
                    Toast.fire({
                        icon: 'success',
                        text: response.message,
                    });
                }
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al actualizar el item. Inténtalo de nuevo.',
                    confirmButtonText: 'Aceptar'
                });

            }
        });
    });

    // --- NUEVO: MANEJADOR PARA EL BOTÓN DE ELIMINAR ---
    $(document).on('click', '#lista-documentacion .delete-item-btn, #lista-escrituracion .delete-item-btn', function () {

        let button = $(this);
        let url = button.data('delete-url');
        let itemId = button.data('item-id');

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
                    url: url,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (response) {
                        if (response.success) {
                            // Eliminar el item de la lista con una animación
                            let listItem = button.closest('li');
                            let listContainer = button.closest('ul');
                            listItem.fadeOut(300, function () {
                                $(this).remove();
                                // Opcional: Si la lista queda vacía, mostrar el mensaje
                                if (listContainer.find('li').length === 0) {
                                    $('#lista-escrituracion').html('<li class="list-group-item text-muted" id="empty-doc-item">Aún no se han agregado documentos a la checklist.</li>');
                                }
                            });
                        }
                        Toast.fire({
                            icon: 'success',
                            text: response.message,
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


    // -- MANEJADOR PARA ENVIAR EL FORMULARIO DE PERITAJE Y APROBACIÓN ---
    $('#form_peritaje').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let data = form.serialize();

        $.ajax({
            url: url,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: "POST",
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                Toast.fire({
                    icon: response.success ? 'success' : 'error',
                    text: response.message,
                });
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error. Inténtalo de nuevo.',
                    confirmButtonText: 'Aceptar'
                });

            }
        });
    });

    // -- MANEJADOR PARA ENVIAR EL FORMULARIO DE DESEMBOLSO ---
    $('#form_desembolso').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let data = form.serialize();

        $.ajax({
            url: url,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: "POST",
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
                $('.input_errors').remove();
            },
            success: function (response) {
                Toast.fire({
                    icon: response.success ? 'success' : 'error',
                    text: response.message,
                });
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function (jqXHR) {
                switch (jqXHR.status) {
                    case 422: // ERROR INPUT VALIDATE
                        $.each(jqXHR.responseJSON.errors, function (i, error) {
                            var el = $(document).find('[name="' + i + '"]');
                            el.after($('<small class="input_errors" style="color: red; font-size: 10px;">' + error[0] + '</small>'));
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
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error. Inténtalo de nuevo.',
                            confirmButtonText: 'Aceptar'
                        });
                }
            }

        });
    });

    // -- MANEJADOR PARA ENVIAR EL FORMULARIO DE ENTREGA ---
    $('#form_acta_entrega').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');
        let data = form.serialize();

        $.ajax({
            url: url,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: "POST",
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
                $('.input_errors').remove();
            },
            success: function (response) {
                Toast.fire({
                    icon: response.success ? 'success' : 'error',
                    text: response.message,
                });
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function (jqXHR) {
                switch (jqXHR.status) {
                    case 422: // ERROR INPUT VALIDATE
                        $.each(jqXHR.responseJSON.errors, function (i, error) {
                            var el = $(document).find('[name="' + i + '"]');
                            el.after($('<small class="input_errors" style="color: red; font-size: 10px;">' + error[0] + '</small>'));
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
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error. Inténtalo de nuevo.',
                            confirmButtonText: 'Aceptar'
                        });
                }
            }

        });
    });

    $('input[name=valor_saldo_reserva]').on('change', function () {
        var valor = $(this).maskMoney('unmasked')[0];
        var procesoVentaId = $('input[name=proceso_venta_id]').val()
        if (valor === '') return;

        $.ajax({
            url: base_url + '/marketing/seguimiento-ventas/guardar-valor-saldo-reserva',
            headers: { 'X-CSRF-TOKEN': csrf },
            method: 'POST',
            data: { 'valor': valor, 'proceso': procesoVentaId },
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        text: response.message,
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        text: 'Ocurrió un error al intentar guardar el valor de saldo reserva.',
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
                            'error',
                        );
                }
            }
        });
    });

    $('#monto-acreditato-banco').on('keyup blur', function () {
        var valorAcreditadoBanco = $(this).maskMoney('unmasked')[0];
        var valorReserva = $('input[name=valor_reserva]').val() || 0;
        var valorSaldoReserva = $('input[name=valor_saldo_reserva]').val() || '0';

        let total = parseFloat(valorReserva) + parseFloat(valorSaldoReserva) + parseFloat(valorAcreditadoBanco);
        $('#total-acreditado').text(formatearUSD(total));
    });

    $('.completar-etapa').on('change', function () {
        var valor = $(this).prop('checked');
        var etapa = $(this).attr('id');
        var procesoVentaId = $('input[name=proceso_venta_id]').val()

        $.ajax({
            url: base_url + '/marketing/seguimiento-ventas/actualizar-etapa/' + procesoVentaId,
            headers: { 'X-CSRF-TOKEN': csrf },
            method: 'PATCH',
            data: { 'valor': valor, 'etapa': etapa },
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        text: response.message,
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        text: 'Ocurrió un error al intentar guardar el valor de saldo reserva.',
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
                            'error',
                        );
                }
            }
        });
    });
});