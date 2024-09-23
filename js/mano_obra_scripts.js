import { getFormData } from './helpers.js';

$(function () {
    $(":input").inputmask();
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#guardar-planificacion').on('click', function () {
        var fecha_inicio = $('input[name=fecha_inicio]').val();
        var fecha_fin = $('input[name=fecha_fin]').val();
        var fecha_inicio_obj = new Date(fecha_inicio);
        var fecha_fin_obj = new Date(fecha_fin);
        var form = $("#form_planificacion_mano_obra");
        var data = getFormData(form);
        var valid = true;

        // Eliminar cualquier borde de error previo
        $('input[name=fecha_inicio], input[name=fecha_fin]').removeClass('error-border');
        $('.error-message').remove();  // Elimina los mensajes de error anteriores
        $('#message').html('');

        if (!fecha_inicio) {
            valid = false;
            $('input:text[name=fecha_inicio]').addClass('error-border');
            $('input:text[name=fecha_inicio]').parent().append('<span class="error-message">Ingrese fecha.</span>');
        }

        if (!fecha_fin) {
            valid = false;
            $('input:text[name=fecha_fin]').addClass('error-border');
            $('input:text[name=fecha_fin]').parent().append('<span class="error-message">Ingrese fecha.</span>');
        }

        if (valid) {
            $.ajax({
                url: 'crear-planificacion',
                headers: { 'X-CSRF-TOKEN': csrf },
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $('#modal-overlay').show();
                },
                success: function (response) {
                    var alert = response.success ? 'success' : 'error';
                    var icon = response.success ? '<i class="fa-regular fa-circle-check"></i> ' : '<i class="fa-regular fa-circle-exclamation"></i> ';

                    if (response.success) {
                        $('tbody').html(response.planificacion);
                    }
                    $('#message').html('<div class="alert alert-' + alert + ' alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h6>' + icon + response.mensaje + '</h6>' +
                        '</div>');

                    $('#modal-overlay').hide();
                    $('[data-toggle="tooltip"]').tooltip();
                    $('[data-toggle="popover"]').popover({ html: true });
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                $('#modal-overlay').hide();
                switch (jqXHR.status) {
                    case 422: // ERROR INPUT VALIDATE
                        $.each(jqXHR.responseJSON.errors, function (i, error) {
                            var el = $(document).find('[name="' + i + '"]');
                            el.addClass('error-border');
                            el.after($('<span class="error-message">' + error[0] + '</span> '));
                        });
                        break;
                    case 419: // ERROR EXPIRATE SESSION
                        window.location = '/';
                        break;

                    default:
                        var errors = JSON.parse(jqXHR.responseText);
                        $('#message').html('<div class="alert alert-danger alert-dismissible">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                            '<h5><i class="icon fas fa-ban"></i> ' + errors + '</h5>' +
                            '</div>');
                }
            });
        }

    });

    $('#modalPlanificacionManoObra').on('hidden.bs.modal', function (e) {
        $('input[name=fecha_inicio], input[name=fecha_fin]').removeClass('error-border');
        $('.error-message').remove();  // Elimina los mensajes de error anteriores
        $('#message').html('');
        $("#form_planificacion_mano_obra")[0].reset();
        //$('#titleArticuloFormModal').text('Nuevo Producto');
        //articulo_id = 0;
    });

    /**
     * Cargar la categoria cuando se seleciona al proveedor
     */
    $('.select2-basic-single').on('change', function (e) {
        // Remueve la clase 'error-border' del contenedor generado por select2
        $(this).closest('.form-group').find('.select2-selection').removeClass('error-border');

        // Elimina solo el mensaje de error asociado con este select2
        $(this).closest('.form-group').find('.error-message').remove();

        if ($(this).attr('id') != 'personal') {
            return;
        }
        // Obtén el valor seleccionado
        var selected_value = $(this).val();
        $.ajax({
            url: url + '/proveedor-articulos',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'proveedor': selected_value },
            beforeSend: function () {
            },
            success: function (response) {
                if (response.success) {
                    // Elimina las opciones anteriores del select
                    let $select = $('#categoria');
                    $select.empty(); // Vacia el select

                    // Itera sobre los artículos y crea nuevas opciones
                    $.each(response.articulos, function (index, articulo) {
                        let option = new Option(articulo.nombre, articulo.id, false, false);
                        $select.append(option); // Añade la opción al select
                    });

                    // Inicializa o actualiza Select2
                    $select.select2({
                        width: '100%'
                    });

                    // Remueve la clase 'error-border' del contenedor generado por select2
                    $($select).closest('.form-group').find('.select2-selection').removeClass('error-border');

                    // Elimina solo el mensaje de error asociado con este select2
                    $($select).closest('.form-group').find('.error-message').remove();
                }

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    // Evento al hacer clic en el botón para clonar
    $('#add-personal').on('click', function () {
        // Eliminar cualquier borde de error previo
        $('#personal, #categoria, #jornada, input[name=valor], input[name=adicional], input[name=descuento], input[name=detalle_adicional], input[name=detalle_descuento]').removeClass('error-border');
        $('.select2-selection').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        var personal = $('#personal option:selected').val();
        var categoria = $('#categoria option:selected').val();
        var jornada = $('#jornada option:selected').val();
        var valor = $('input:text[name=valor]').val();
        var adicional = $('input:text[name=adicional]').val();
        var descuento = $('input:text[name=descuento]').val();
        var detalle_adicional = $('input:text[name=detalle_adicional]').val();
        var detalle_descuento = $('input:text[name=detalle_descuento]').val();

        var valid = true;

        if (!personal) {
            valid = false;
            $('#personal').next('.select2-container').find('.select2-selection').addClass('error-border'); // Agregar borde rojo a select2
            $('#personal').parent().append('<span class="error-message">Por favor seleccione el personal.</span>');  // Añadir mensaje de error
        }

        if (!categoria) {
            valid = false;
            $('#categoria').next('.select2-container').find('.select2-selection').addClass('error-border'); // Agregar borde rojo a select2
            $('#categoria').parent().append('<span class="error-message">Por favor seleccione la categoría.</span>');  // Añadir mensaje de error
        }

        if (!jornada) {
            valid = false;
            $('#jornada').next('.select2-container').find('.select2-selection').addClass('error-border'); // Agregar borde rojo a select2
            $('#jornada').parent().append('<span class="error-message">Por favor seleccione la jornada.</span>');  // Añadir mensaje de error
        }

        if (!valor) {
            valid = false;
            $('input:text[name=valor]').addClass('error-border');
            $('input:text[name=valor]').parent().append('<span class="error-message">Ingrese valor.</span>');  // Añadir mensaje de error
        }

        if (adicional && !detalle_adicional) {
            valid = false;
            $('input:text[name=detalle_adicional]').addClass('error-border');
            $('input:text[name=detalle_adicional]').parent().append('<span class="error-message">Por favor ingrese un detalle adicional.</span>');  // Añadir mensaje de error
        }

        if (descuento && !detalle_descuento) {
            valid = false;
            $('input:text[name=detalle_descuento]').addClass('error-border');
            $('input:text[name=detalle_descuento]').parent().append('<span class="error-message">Por favor ingrese un detalle de descuento.</span>');  // Añadir mensaje de error
        }


        if (!valid) {
            return;
        }
        var opcionesPersonal = "", opcionesCategoria, opcionesJornada;
        $('#personal option').each(function () {
            // Clona el option actual
            let option = $(this).clone();

            // Convierte el option a string para almacenarlo
            opcionesPersonal += option.prop('outerHTML');
        });

        $('#categoria option').each(function () {
            // Clona el option actual
            let option = $(this).clone();

            // Convierte el option a string para almacenarlo
            opcionesCategoria += option.prop('outerHTML');
        });

        $('#jornada option').each(function () {
            // Clona el option actual
            let option = $(this).clone();

            // Convierte el option a string para almacenarlo
            opcionesJornada += option.prop('outerHTML');
        });

        var nuevaFila = `<tr class="elementos-agregados">
                    <td>
                        <select name="personal[]" class="form-control select2-basic-single"
                            data-placeholder="Selecione personal">
                            ${opcionesPersonal}
                            
                        </select>
                    </td>
                    <td>
                        <select name="categoria[]" class="form-control select2-basic-single"
                            data-placeholder="Selecione categoría">
                            ${opcionesCategoria}
                        </select>
                    </td>
                    <td>
                        <select name="jornada[]" class="form-control select2-basic-single"
                            data-placeholder="Selecione jornada">
                            ${opcionesJornada}
                        </select>
                    </td>
                    <td>
                    <input name="valor[]" type="text" class="form-control input-double" placeholder="$ 0.00" data-inputmask="'alias': 'currency'" value="${valor}">
                    </td>
                    <td>
                        <input name="adicional[]" type="text" class="form-control input-double" placeholder="$ 0.00" data-inputmask="'alias': 'currency'" value="${adicional}">
                    </td>
                    <td>
                        <input name="detalle_adicional[]" type="text" class="form-control" value="${detalle_adicional}">
                    </td>
                    <td>
                        <input name="descuento[]" type="text" class="form-control input-double" placeholder="$ 0.00" data-inputmask="'alias': 'currency'" value="${descuento}">
                    </td>
                    <td>
                        <input name="detalle_descuento[]" type="text" class="form-control" value="${detalle_descuento}">
                    </td>
                    <td>
                        <input name="observaciones[]" type="text" class="form-control input-double">
                    </td>
                    <td>
                        <button type="button" class="btn btn-dark btn-remove"> - </button>
                    </td>
                </tr>`;


        $('tbody').append(nuevaFila);
        $('#tr-default').hide();

        // Inicializa Select2 para cada nuevo select
        let nuevoSelectPersonal = $('table tr:last select[name="personal[]"]');
        let nuevoSelectCategoria = $('table tr:last select[name="categoria[]"]');
        let nuevoSelectJornada = $('table tr:last select[name="jornada[]"]');

        // Inicializa select2 para cada select nuevo
        nuevoSelectPersonal.select2({ width: '100%' });
        nuevoSelectCategoria.select2({ width: '100%' });
        nuevoSelectJornada.select2({ width: '100%' });

        // Establece el valor seleccionado en el nuevo select2
        nuevoSelectPersonal.val(personal).trigger('change');
        nuevoSelectCategoria.val(categoria).trigger('change');
        nuevoSelectJornada.val(jornada).trigger('change');
        $(":input").inputmask();
    });

    /**
     * Remover filta de tabla
     */
    $(document).on('click', '.btn-remove', function () {
        $(this).closest('tr').remove();
        var numeroFila = $('.elementos-agregados').length;
        // Mostrar el mensaje de que no hay elementos si no hay filas
        if (numeroFila == 0) {
            $('#tr-default').show();
        }
    });

    /**
     * Editar mano de obra
     */

    $(document).on('click', '.editar-empleados-mano-obra', function () {
        var id = $(this).attr('id');

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-dark mx-2",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Seleccione una fecha",
            html: `
                <select id="select2-dropdown" class="form-control">
                    <option value="" disabled selected>Cargando...</option>
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: "Aceptar",
            cancelButtonText: "Cancelar",
            showLoaderOnConfirm: true,
            preConfirm: () => {
                // Obtener el valor seleccionado de Select2
                let selectedValue = $('#select2-dropdown').val();

                // Validar si el valor es nulo o vacío
                if (!selectedValue) {
                    Swal.showValidationMessage('Debe seleccionar una fecha antes de continuar');
                    return false; // Evita que se cierre el modal
                }

                // Si se seleccionó una opción, devuelve el valor
                return selectedValue;
            },
            didOpen: () => {
                // Inicializar Select2 en el dropdown dentro del SweetAlert2
                $('#select2-dropdown').select2({
                    dropdownParent: $('.swal2-container') // Asegura que el dropdown se muestre correctamente
                });
                $.ajax({
                    url: url + '/fechas-planificacion',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'GET',
                    data: { 'mano_obra': id },
                    success: function (data) {
                        // Limpiar el select
                        $('#select2-dropdown').empty();
                        if (data.success) {
                            // Agregar un placeholder
                            $('#select2-dropdown').append('<option value="" disabled selected>Seleccione una fecha</option>');
                            // Rellenar el select con las opciones obtenidas
                            $.each(data.fechas, function (key, value) {
                                $('#select2-dropdown').append('<option value="' + value.id + '">' + value.nombre + '</option>');
                            });
                        } else {
                            $('#select2-dropdown').append('<option value="" disabled selected>Seleccione una fecha</option>');
                            $('#select2-dropdown').attr('disabled', true)
                            Swal.showValidationMessage('No existen fechas programadas');
                        }
                    },
                    error: function () {
                        $('#select2-dropdown').empty();
                        $('#select2-dropdown').append('<option value="">Error al cargar fechas</option>');
                    }
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    var errors = JSON.parse(jqXHR.responseText);
                    console.log(errors)
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Obtener el valor seleccionado de Select2
                let selectedValue = $('#select2-dropdown').val();
                if (selectedValue != null) {
                    window.location.href = url + '/editar-planificacion-trabajadores/' + id + '/' + selectedValue;
                } else {
                    $('#select2-dropdown').find('<pand>seleciones una fecha</spand>')
                }

            }
        });
    });
});