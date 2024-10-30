$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var estadoSeleccionado = 0;

    $('#inventarioFormModal').on('shown.bs.modal', function (e) {
        console.log("cargo modal");
        $('.select2-basic-single').select2({
            dropdownParent: $('#inventarioFormModal') // Asegúrate de usar el ID del modal
        });

        $(e.target).find('select.select2-tag').select2('destroy').select2({
            allowClear: false, // Permite limpiar la selección
            tags: true, // Permite agregar nuevas opciones
            placeholder: function () {
                $(this).data('placeholder');
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true // add additional parameters
                }
            },
            insertTag: function (data, tag) {
                // Insertar la nueva opción al principio
                data.unshift(tag);
            },
            dropdownParent: $(e.target) // Solo ajustar el dropdownParent en los modales abiertos
        });

    });

    $('#inventarioFormModal').on('hidden.bs.modal', function (e) {
        $('#message').hide();
        $("#from-articulo")[0].reset();
        $('#titleFormModal').text('Nuevo Producto');
    });

    $('input[name="estado"]').on('change', function () {
        estadoSeleccionado = $('input[name="estado"]:checked').val();
    });

    $('#guardar').on('click', function () {
        var $button = $(this); // Guarda referencia al botón
        var $spinner = $('#spinner'); // Guarda referencia al spinner
        var $message = $('#message');
        var producto = $('select[name=producto]').val();
        var estado = estadoSeleccionado;
        var cantidad = $('input:text[name=cantidad]').val();
        var valid = true;

        $('select[name=producto], input[name="estado"], input[name=cantidad]').removeClass('error-border');
        $('.select2-selection').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores
        $message.addClass('d-none'); // oculta el mesaje de alerta


        if (producto == "") {
            var valid = false;
            $('select[name=producto]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=producto]').parent().append('<span class="error-message">Seleccione el producto.</span>');
        }

        if (cantidad == "") {
            var valid = false;
            $('input:text[name=cantidad]').addClass('error-border');
            $('input:text[name=cantidad]').parent().append('<span class="error-message">Ingrese cantidad.</span>');
        }

        if (estado == 0) {
            var valid = false;
            $('input:radio[name=estado]').last().parent().parent().after('<span class="error-message">Seleccione el esatdo.</span>');
        }

        if (!valid) {
            return;
        }

        var data = {
            "producto": producto,
            "cantidad": cantidad,
            "estado": estado
        };

        $.ajax({
            url: 'inventario/nuevo',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                // Deshabilitar el botón y mostrar el spinner
                $button.prop('disabled', true);
                $spinner.removeClass('d-none');
            },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $('tbody').html(response.inventario);
                    $message.append('<span>' + response.mensaje + '</span>');
                    $message.addClass('alert-success');
                    $message.removeClass('d-none');
                } else {
                    $message.append('<span >' + response.mensaje + '</span>');
                    $message.addClass('alert-danger');
                    $message.removeClass('d-none');
                }

                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
                // Habilitar el botón y ocultar el spinner
                $button.prop('disabled', false);
                $spinner.addClass('d-none');
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            // Habilitar el botón y ocultar el spinner
            $button.prop('disabled', false);
            $spinner.addClass('d-none');
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    $('input:text[name=inventario_search]').on('keyup', function () {
        var $value = $(this).val();

        $.ajax({
            url: 'inventario/buscar',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value },
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

    $(document).on('click', '.eliminar-inventario', function () {
        var $id = $(this).attr('id');

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-dark mx-2",
                cancelButton: "btn btn-secondary",
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
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
                    url: 'inventario/eliminar/' + $id,
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

                            $("#" + $id).remove();

                            if ($('tbody').children().length == 0) {
                                $('tbody').html('<tr>' +
                                    '<td colspan = "6" class="text-center">No se encontraron datos para mostrar.</td>' +
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
        })
    });

    $(document).on('click', '.eliminar-inventario-producto', function () {
        var $id = $(this).attr('id');

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-dark mx-2",
                cancelButton: "btn btn-secondary",
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
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
                    url: 'eliminar/' + $id,
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

                            $("#" + $id).remove();

                            if ($('tbody').children().length == 0) {
                                $('tbody').html('<tr>' +
                                    '<td colspan = "6" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
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
        })
    });

    $(document).on('click', '.dar_baja', function () {
        var $id = $(this).attr('id');

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-dark mx-2",
                cancelButton: "btn btn-secondary",
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            text: "Ingrese la cantidad del producto que desea dar de baja",
            input: "text",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar",
            showLoaderOnConfirm: true,
            didOpen: () => {
                const inputField = Swal.getInput();
                inputField.classList.add('mi-clase-personalizada');

                // Aplicar InputMask para formato de tipo double (decimal)
                $(inputField).inputmask({
                    alias: "integer",
                    allowMinus: false, // No permite números negativos
                    rightAlign: false,
                });
            },
            preConfirm: async (cantidad) => {
                try {
                    if (!cantidad) {
                        return Swal.showValidationMessage('Debe ingresar la cantidad');
                    }

                    return $.ajax({
                        headers: { 'X-CSRF-TOKEN': csrf },
                        url: 'dar-de-baja',
                        method: 'POST',
                        data: {
                            id: $id,
                            cantidad: cantidad
                        },
                        success: function(response) {
                            if(!response.success){
                                Swal.showValidationMessage(`${response.mensaje}`);
                            }
                        },
                        error: function(error) {
                            Swal.showValidationMessage(`Error en la petición: ${error}`);
                        }
                    })
                    .then(response => {
                        if (response.error) {
                            Swal.showValidationMessage(`Error: ${response.error}`);
                        }
                        return response; // Devuelve la respuesta si todo va bien
                    })
                } catch (error) {
                    Swal.showValidationMessage(`
                  Request failed: ${error}
                `);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if(result.isConfirmed){
                if(result.value.success){
                    Swal.fire({
                        icon: 'success',
                        title: '',
                        text: result.value.mensaje,
                    });
                }
            }
        });
    });
});