import { getFormData } from './helpers.js';
$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var reloadPage = false;
    var dataEdit = '';

    $('#modalRubrosPresupuesto').on('shown.bs.modal', function (e) {
        $('.select2-tag').select2({
            width: '100%',
            dropdownParent: $('#modalRubrosPresupuesto'),
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
            }
        });
    });

    $('#modalRubrosPresupuesto').on('hidden.bs.modal', function (e) {
        if (reloadPage) {
            location.reload(); // Recarga la página
        }
        $('#message').html('');
        $('.input_errors').remove();
        $('select[name=categoria_rubro], select[name=rubro], select[name=unidad_medida], input[name=cantidad], input[name=valor]').removeClass('error-border');
        $('.select2-tag').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();

    });


    //** Cargar la categoria del rubro selecionado */
    $('#categoria').on('change', function (e) {
        // Remueve la clase 'error-border' del contenedor generado por select2
        $(this).closest('.form-group').find('.select2-selection').removeClass('error-border');
        // Elimina solo el mensaje de error asociado con este select2
        $(this).closest('.form-group').find('.error-message').remove();

        let $select = $('#rubros');
        $select.empty(); // Vacia el select rubros

        // Obtén el valor seleccionado
        var selected_value = $(this).val();
        if (isNaN(selected_value)) {
            return;
        }

        $.ajax({
            url: '/rubros-presupuesto',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'categoria': selected_value },
            beforeSend: function () {
            },
            success: function (response) {
                // Itera sobre los artículos y crea nuevas opciones
                $.each(response.rubros, function (index, rubro) {
                    let option = new Option(rubro, index, false, false);
                    $select.append(option); // Añade la opción al select
                });

                $select.trigger('change');
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });


    //** Guardar rubro */
    $("#guardar").on('click', function () {
        var form = $("#form_rubros_presupuesto");
        var data = getFormData(form);
        var endPoint = 'store';
        var type = 'POST';
        var valid = true;

        $('select[name=categoria_rubro], select[name=rubro], select[name=unidad_medida], input[name=cantidad], input[name=valor]').removeClass('error-border');
        $('.select2-tag').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        if (data.categoria_rubro == "") {
            var valid = false;
            $('select[name=categoria_rubro]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=categoria_rubro]').parent().append('<span class="error-message">Seleccione la categoría.</span>');
        }
        if (data.rubro == "") {
            var valid = false;
            $('select[name=rubro]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=rubro]').parent().append('<span class="error-message">Seleccione el rubro.</span>');
        }
        if (data.unidad_medida == "") {
            var valid = false;
            $('select[name=unidad_medida]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=unidad_medida]').parent().append('<span class="error-message">Seleccione la unidad de medida.</span>');
        }


        if (data.cantidad == "") {
            var valid = false;
            $('input:text[name=cantidad]').addClass('error-border');
            $('input:text[name=cantidad]').parent().append('<span class="error-message">Ingrese la cantidad.</span>');
        }

        if (data.valor == "") {
            var valid = false;
            $('input:text[name=valor]').addClass('error-border');
            $('input:text[name=valor]').parent().append('<span class="error-message">Ingrese el valor.</span>');
        }

        if (!valid) return;

        if (dataEdit != '') {
            endPoint = 'actualizar/' + dataEdit.data('prestamo');
            type = 'PUT';
        }

        $.ajax({
            url: endPoint,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: type,
            data: data,
            beforeSend: function () {

            },
            success: function (response) {
                if (response.success) {
                    reloadPage = true;
                    $('#message').html('<div class="alert alert-success alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-check"></i> ' + response.mensaje + '</h5>' +
                        '</div>');
                } else {
                    reloadPage = false;
                    $('#message').html('<div class="alert alert-danger alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-ban"></i> ' + response.mensaje + '</h5>' +
                        '</div>');
                }
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            reloadPage = false;
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors);
        });
    });

    ///** Remover rubros  (Esta acción eliminará la categoría y todos los rubros asociados, ¿Desea continuar?)*/
    $('.remove-rubro').click(function () {
        var rubro_id = $(this).data('id');

        Swal.fire({
            title: '¿Esta Seguro?',
            text: "Esta acción eliminará el rubro del presupuesto, ¿Desea continuar?",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/eliminar-rubro-presupesto/' + rubro_id,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    beforeSend: function () {

                    },
                    success: function (response) {
                        Swal.fire({
                            icon: response.success ? "success" : "error",
                            text: response.mensaje,
                            confirmButtonText: 'Aceptar',
                        }).then((result) => {
                            location.reload();
                        });

                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    var errors = JSON.parse(jqXHR.responseText);
                    console.log(errors);
                });
            }
        });
    });

    ///** Remover rubros  (Esta acción eliminará la categoría y todos los rubros asociados, ¿Desea continuar?)*/
    $('.remove-categoria').click(function () {
        var categoria_id = $(this).data('id');
        var proyecto_id = $(this).data('proyecto_id');

        Swal.fire({
            title: '¿Esta Seguro?',
            text: "Esta acción eliminará el rubro del presupuesto, ¿Desea continuar?",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/eliminar-categoria-presupesto/' + categoria_id,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    data: { 'proyecto': proyecto_id },
                    beforeSend: function () {

                    },
                    success: function (response) {
                        Swal.fire({
                            icon: response.success ? "success" : "error",
                            text: response.mensaje,
                            confirmButtonText: 'Aceptar',
                        }).then((result) => {
                            location.reload();
                        });

                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    var errors = JSON.parse(jqXHR.responseText);
                    console.log(errors);
                });
            }
        });
    });

    ///**  Actualizar el procentaje del costo indirecto del proyecto */
    $('#table-rubros-presupuesto tfoot td.editar-costo-indirecto').click(function () {
        var proyecto_id = $(this).data('id');
        var porcentaje = $(this).data('porcentaje');

        Swal.fire({
            title: '¿Esta Seguro?',
            text: "Esta acción actualizará el porcentaje (%) del costo indirecto del proyecto, ¿Desea continuar?",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Cancelar',
            input: "text",
            inputValue: porcentaje,
            inputAttributes: {
                autocapitalize: "off",
            },
            customClass: {
                input: 'form-control ' // Agrega la clase aquí
            },
            didOpen: () => {
                // Inicializa Inputmask en el input de SweetAlert2
                const input = Swal.getInput();
                if (input) {
                    // Aplica Inputmask al input
                    Inputmask('numeric', {
                        rightAlign: false,
                        allowMinus: false,
                        digits: 0,
                        min: 0,
                        max: undefined,
                        integerDigits: undefined,
                        placeholder: "",
                        autoUnmask: true
                    }).mask(input);
                }
            },
            preConfirm: (value) => {
                if (!value) {
                    // Si el campo está vacío, devuelve una promesa rechazada para evitar que el modal se cierre
                    return Swal.showValidationMessage('Debe ingresar un valor.');
                }
                // Si todo está bien, devuelve el nombre ingresado
                return value;
            }
        }).then((result) => {
            if (result.value && result.isConfirmed) {
                $.ajax({
                    url: '/actualizar-costo-indirecto/' + proyecto_id,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'PUT',
                    data: { 'porcentaje': result.value },
                    beforeSend: function () {

                    },
                    success: function (response) {
                        Swal.fire({
                            icon: response.success ? "success" : "error",
                            text: response.mensaje,
                            confirmButtonText: 'Aceptar',
                        }).then((result) => {
                            location.reload();
                        });

                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    reloadPage = false;
                    var errors = JSON.parse(jqXHR.responseText);
                    console.log(errors);
                });

            }
        });
    });

    $('input:text[name=rubros_search]').on('keyup', function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var $value = $(this).val();
        var proyecto_id = $(this).data('proyecto_id');

        $.ajax({
            url: '/filtrar-rubros-presupuesto',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'filtro': $value, 'proyecto': proyecto_id },
            beforeSend: function () {
            },
            success: function (data) {
                console.log(data);
                //$('tbody').html(data);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });
});