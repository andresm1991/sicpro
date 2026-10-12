import { getFormData, limpiarValores } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    // Obtén la URL completa
    let url = window.location.href;

    // Divide la URL por cada "/"
    let segments = url.split('/');

    // Obtén el último segmento
    let lastSegment = segments.pop() || segments.pop();  // Maneja caso de '/' al final


    var numeroFila = 0;

    /**
     * Filtrar adquisiciones
     */

    $('input:text[name=adquisicion_search]').on('keyup', function () {
        var $value = $(this).val();
        var $tipo = $(this).attr('id');

        $.ajax({
            url: base_url + '/administrativo/adquisiciones/operativo/buscar-adquisicion',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value, 'tipo': $tipo },
            beforeSend: function () {
            },
            success: function (data) {
                $('#table-list-pedidos-' + $tipo + ' tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            error: function (xhr, status, error) {
                console.error("Error en la solicitud AJAX:", error);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    /**
     * Scripts Contratistas 
     */
    // **Actualizar el estado de pagado**
    $(document).on('change', '.pagado', function () {
        const $checkbox = $(this); // Almacenar el elemento actual
        var id_pago = $(this).val();

        if ($checkbox.is(':checked')) {
            message().then((resultado) => {
                if (resultado) {
                    $.ajax({
                        url: base_url + '/pago_orden_trabajo/' + id_pago,
                        headers: { 'X-CSRF-TOKEN': csrf },
                        type: 'PUT',
                        data: { 'id_pago': id_pago },
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

    // **Buscar contratista**
    $('input:text[name=orden_contratista_search]').on('keyup', function () {
        var $value = $(this).val();
        var $tipo = $(this).attr('id');

        $.ajax({
            url: base_url + '/administrativo/buscar-orden-trabajo',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value, 'tipo': $tipo },
            beforeSend: function () {
            },
            success: function (data) {
                $('#table-' + $tipo + ' tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });


    // **Buscar mano obra**
    $('input:text[name=mano_obra_search]').on('keyup', function () {
        var $value = $(this).val();
        var $tipo = $(this).attr('id');

        $.ajax({
            url: base_url + '/administrativo/buscar-mano-obra',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value, 'tipo': $tipo },
            beforeSend: function () {
            },
            success: function (data) {
                $('#table_' + $tipo + ' tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });


    //** Agregar elementos a la tabla de pedidos */
    $('#add-producto-adquisicion').on('click', function () {
        var producto_id = $('#productos').val();
        var producto = $('#productos option:selected').text();
        var unidad_medida_id = $('#unidad_medida').val();
        var unidad_medida = $('#unidad_medida option:selected').text();
        var cantidad = $('#cantidad').val();
        var valor_unitario = $('#valor_unitario').val();
        var iva = $('#iva').val();
        var necesidad = $('#necesidad option:selected').text();

        var valid = true;
        $('select[name=productos], select[name=unidad_medida], select[name=necesidad], input[name=cantidad], input[name=valor_unitario], input[name=iva]').removeClass('error-border');
        $('.select2-tag').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        if (producto == "") {
            var valid = false;
            $('select[name=productos]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=productos]').parent().append('<span class="error-message">Seleccione el producto.</span>');
        }
        if (unidad_medida == "") {
            var valid = false;
            $('select[name=unidad_medida]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=unidad_medida]').parent().append('<span class="error-message">Seleccione el opción.</span>');
        }

        if (cantidad == "" || cantidad == 0) {
            var valid = false;
            $('input:text[name=cantidad]').addClass('error-border');
            $('input:text[name=cantidad]').parent().append('<span class="error-message">Ingrese cantidad.</span>');
        }
        if (valor_unitario == "" || valor_unitario == 0) {
            var valid = false;
            $('input:text[name=valor_unitario]').addClass('error-border');
            $('input:text[name=valor_unitario]').parent().append('<span class="error-message">Ingrese valor.</span>');
        }

        if (iva == "") {
            var valid = false;
            $('input:text[name=iva]').addClass('error-border');
            $('input:text[name=iva]').parent().append('<span class="error-message">IVA no puede estar vacio.</span>');
        }

        if (necesidad == "") {
            var valid = false;
            $('select[name=necesidad]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=necesidad]').parent().append('<span class="error-message">Seleccione o ingrese la necesidad.</span>');
        }

        if (!valid) {
            return;
        }

        numeroFila = $('.elementos-agregados').length + 1;
        // Calcular el subtotal
        let subtotal = limpiarValores(cantidad) * limpiarValores(valor_unitario);

        // Calcular el total con IVA
        let totalConIva = parseFloat(subtotal) * (1 + parseFloat(iva) / 100);

        // Crear una nueva fila con los datos
        var nuevaFila = `
            <tr class="elementos-agregados">
                <td>${numeroFila}</td>
                <td>${producto}</td>
                <td>
                    <span>${cantidad}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2 input-double" name="cantidad[]"
                            value="${cantidad}">
                        <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </td>
                <td>
                    ${unidad_medida}
                    <input type="hidden" name="unidad_medida[]" value="${unidad_medida_id}">
                </td>
                <td>
                    <span>$ ${valor_unitario}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2 input-double" name="valor_unitario[]"
                            value="${valor_unitario}">
                        <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </td>
                <td class="edit-item col-gasolina">
                    <span>0</span>
                    <div class="d-flex align-items-center hidden">
                            <input type="text" class="form-control mr-2" name="km[]"
                                value="">
                            <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                    class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                </td>
                <td>
                    ${iva}%
                    <input type="hidden" name="iva_producto[]" value="${iva}">
                </td>
                <td class="total_unitario">
                   ${formatearUSD(totalConIva)}
                    <input type="hidden" name="total[]" value="${totalConIva.toFixed(2)}">
                </td>
                <td>
                    <span>${necesidad}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2" name="necesidad[]"
                            value="${necesidad}">
                        <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="checkbox-wrapper-8 d-flex justify-content-center align-items-center">
                    <input type="hidden" name="inventario[${numeroFila - 1}]" value="0">

                    <input class="tgl tgl-skewed inventario" name="inventario[${numeroFila - 1}]" id="cb3-${numeroFila - 1}" type="checkbox" value="0"/>
                        <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="cb3-${numeroFila - 1}"></label>
                        
                    </div>
                </td>
                <td class="align-middle table-actions">
                    <div class="action-buttons">
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto" id=""><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </td>
                <input type="hidden" name="productos[]" value="${producto_id}">
            </tr>
        `;
        $('tbody').append(nuevaFila);
        calcularTotal();

        // Deshabilitar la opción seleccionada
        //$('#productos option[value="' + producto_id + '"]').prop('disabled', true);
        $('#tr-default').hide();

        // Condición para mostrar u ocultar la columna de gasolina en todas las filas
        if (producto === 'gasolina para camioneta') {
            $('th.col-gasolina').show();
            $('td.col-gasolina').show();
        } else {
            $('th.col-gasolina').hide();
            $('td.col-gasolina').hide();
        }
        // Limpiar campos 
        clearInputs();

    });

    /**
     * Eliminar elementos de la tabal de pedidos
     */
    $(document).on('click', ".eliminar-fila-producto", function () {
        var producto_id = $(this).closest('tr').find('input:hidden[name="productos[]"]').val();
        $(this).closest('tr').remove();

        // Actualizar los números de fila
        $('.elementos-agregados').each(function (index) {
            $(this).find('td:first').text(index + 1);
        });
        numeroFila = $('.elementos-agregados').length;
        // Mostrar el mensaje de que no hay elementos si no hay filas
        if (numeroFila == 0) {
            $('#tr-default').show();
        }

        calcularTotal();
        // habilitar la opción seleccionada
        //$('#productos option[value="' + producto_id + '"]').prop('disabled', false);
        //$("#productos").selectpicker("refresh");
    });

    /**
     * Editar elementos de td items en orden de pedido
    */
    $(document).on('click', ".edit-item", function () {
        // Encuentra el td contenedor
        var $td = $(this).closest('td.edit-item');
        // Encuentra el input dentro del td y obtén su valor
        var inputValue = $td.find('input').val();
        // Primero, ocultar todos los divs en otros td y mostrar sus spans
        $('.edit-item div').addClass('hidden'); // Ocultar todos los divs con la clase 'hidden'
        $('.edit-item span').show(); // Mostrar todos los span

        // Ocultar el span y mostrar el div solo en el td clicado
        $(this).find('span').hide(); // Ocultar el span en el td actual
        $(this).find('div').removeClass('hidden'); // Mostrar el div en el td actual
    });

    // Botón "aceptar" para confirmar el cambio
    $(document).on('click', '.aceptar', function (event) {
        event.stopPropagation(); // Evitar que se dispare el evento del <td>

        // Obtener el valor del input
        var newValue = $(this).closest('.edit-item').find('input').val();

        // Actualizar el valor del span
        $(this).closest('.edit-item').find('span').text(newValue);

        // Ocultar el div y mostrar el span nuevamente
        $(this).closest('div').addClass('hidden');
        $(this).closest('.edit-item').find('span').show();
    });

    // Botón "cancelar" para cancelar el cambio
    $(document).on('click', '.cancelar', function (event) {
        event.stopPropagation(); // Evitar que se dispare el evento del <td>

        // Ocultar el div y mostrar el span nuevamente sin hacer cambios
        $(this).closest('div').addClass('hidden');
        $(this).closest('.edit-item').find('span').show();
    });

    function clearInputs() {
        $('#productos').val(null).trigger('change');
        $('#unidad_medida').val(null).trigger('change');
        $('#necesidad').val(null).trigger('change');
        $('#cantidad').val(null);
        $('#valor_unitario').val(null);
        $('#iva').val(0);


        var tipo_etapa = $('input:hidden[name=slug_adquisicion]').val();
        if (tipo_etapa == 'SERVICIOS') {
            $('#unidad_medida').val(null).trigger('change');
            $('input:text[name=precio_unitario]').val("");
        }
    }

    $('#form_order_pedido').on('submit', function (e) {
        e.preventDefault(); // Detiene el envío por defecto
        var completo = $('input[name=orden_completa]').is(':checked');
        var forma_pago = $('input[name=forma_pago]').is(':checked');

        var valid = true;
        $('select[name=proyecto], select[name=etapa], select[name=actividad], select[name=proveedor], input[name=numero_factura]').removeClass('error-border');
        $('.select2-basic-single').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        if ($('select[name=proyecto]').val() == "") {
            var valid = false;
            $('select[name=proyecto]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=proyecto]').parent().append('<span class="error-message">Seleccione proyecto.</span>');
        }

        if ($('select[name=etapa]').val() == "") {
            var valid = false;
            $('select[name=etapa]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=etapa]').parent().append('<span class="error-message">Seleccione etapa.</span>');
        }

        if ($('select[name=actividad]').val() == "") {
            var valid = false;
            $('select[name=actividad]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=actividad]').parent().append('<span class="error-message">Seleccione tipo.</span>');
        }

        if ($('select[name=tipo_costo]').val() == "") {
            var valid = false;
            $('select[name=tipo_costo]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=tipo_costo]').parent().append('<span class="error-message">Seleccione tipo de costo.</span>');
        }


        if ($('select[name=proveedor]').val() == "") {
            var valid = false;
            $('select[name=proveedor]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=proveedor]').parent().append('<span class="error-message">Seleccione proveedor.</span>');
        }

        if ($('input[name=numero_factura]').val() == "" && completo) {
            var valid = false;
            $('input:text[name=numero_factura]').addClass('error-border');
            $('input:text[name=numero_factura]').parent().append('<span class="error-message">Ingrese factura.</span>');
        }

        if ($('select[name=subproyecto]').val() != "" && $('input[name=nro_proforma]').val() == "" && completo) {
            var valid = false;
            $('input:text[name=nro_proforma]').addClass('error-border');
            $('input:text[name=nro_proforma]').parent().append('<span class="error-message">Ingrese proforma.</span>');
        }

        if (!forma_pago) {
            var valid = false;
            $('<span>', {
                text: 'Seleccione forma de pago.',
                class: 'error-message'
            }).insertAfter($('input[name=forma_pago]').closest('.select_wrapper'));
        }

        if (!valid) {
            return;
        }

        // Enviar formulario si todo está bien
        this.submit();
    });

    $("#form_order_recepcion").on("submit", function (event) {

        $(".input_errors").remove();

        let proyecto = $('#proyecto').data('proyecto');
        let subproyecto = $('input[name=subproyecto]').attr('id');
        let nroProforma = $('input[name=nro_proforma]').val().trim();

        if (proyecto.toLowerCase().trim() == 'adecentamientos' && subproyecto != '' && nroProforma == '') {
            event.preventDefault();
            $('input[name=nro_proforma]').after($('<small class="input_errors" style="color: red;">campo requerido.</small>'));
        }

    });

    $(document).on('click', '.eliminar-adquisicion', function () {
        let adquisicionId = $(this).attr('id');
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
                    url: 'eliminar/' + adquisicionId,
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

                            $("#table-list-pedidos-pendientes #" + id).remove();

                            if ($('#table-list-pedidos-pendientes tbody').children().length == 0) {
                                $('#table-list-pedidos-pendientes tbody').html('<tr>' +
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
        });
    });

    $(document).on('shown.bs.modal', '#agregarProductosModal', function () {

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

    $('#agregar-producto').on('click', function () {
        var form = $("#form_agregar_productos");
        var data = getFormData(form);

        var valid = true;

        $('select[name=producto], select[name=unidad_medida], select[name=necesidad],  input[name=cantidad], input[name=valor_unitario]').removeClass('error-border');
        $('.select2-tag').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        if (data.producto == "") {
            var valid = false;
            $('select[name=producto]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=producto]').parent().append('<span class="error-message">Seleccione el producto.</span>');
        }
        if (data.necesidad == "") {
            var valid = false;
            $('select[name=necesidad]').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('select[name=necesidad]').parent().append('<span class="error-message">Seleccione o ingrese una necesidad.</span>');
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

        if (data.valor_unitario == "" || data.valor_unitario == 0) {
            var valid = false;
            $('input:text[name=valor_unitario]').addClass('error-border');
            $('input:text[name=valor_unitario]').parent().append('<span class="error-message">Ingrese un valor mayor a 0.</span>');
        }

        if (!valid) return;

        numeroFila = $('.elementos-agregados').length + 1;
        // Calcular el subtotal
        let subtotal = parseFloat(data.cantidad) * parseFloat(data.valor_unitario);
        // Calcular el total con IVA
        let totalConIva = subtotal * (1 + parseFloat(data.iva) / 100);

        // Seleccionar el último <select> con nombre que comience con "unidad_medida"
        const unidadMedidaSelect = $('select[name^="unidad_medida"]').last();
        // Crear el nuevo <select> dinámicamente
        const nuevoSelectHTML = `<select name="unidad_medida[${numeroFila - 1}]" class="form-control col-sm-12 select2-tag" data-placeholder="Seleccione">${unidadMedidaSelect.find('option').map(function () {
            return `<option value="${$(this).val()}" ${$(this).val() == data.unidad_medida ? 'selected' : ''}>${$(this).text()}</option>`;
        }).get().join('')}</select>`;

        // Crear una nueva fila con los datos
        var nuevaFila = `
            <tr class="elementos-agregados">
                <td class="align-middle">${numeroFila}</td>
                <td class="align-middle">
                ${$('#producto option:selected').text()}
                <input type="hidden" name="productos[${numeroFila - 1}]" value="${data.producto}">
                </td>
                <td class="align-middle text-center cantidad col-md-1 col-12" data-index = "${numeroFila - 1}">
                    <input type="text" class="form-control input-double" name="cantidad[${numeroFila - 1}]" value="${data.cantidad}" placeholder="0" data-index = "${numeroFila - 1}">
                </td>
                <td class="align-middle">
                    ${nuevoSelectHTML}
                </td>
                <td class="align-middle col-md-1 col-12">
                    <input type="text" class="form-control currency precio-unitario" name="valor[${numeroFila - 1}]"
                        value="${data.valor_unitario}" placeholder="$ 0.00" data-index = "${numeroFila - 1}">
                </td>
                
                <td class="align-middle col-md-1 col-12">
                    <input type="text" class="form-control col-sm-12 input-enteros iva-producto" name="iva_producto[${numeroFila - 1}]" value="${data.iva}" placeholder="0" data-index = "${numeroFila - 1}">
                </td>
                <td class="align-middle col-md-1 col-12">
                    <input type="text" class="form-control col-sm-12 input-enteros costo-indirecto" name="indirecto[${numeroFila - 1}]" value="${data.indirecto}" placeholder="0" data-index = "${numeroFila - 1}">
                </td>
                <td class="align-middle calculo-total" data-index="${numeroFila - 1}">
                     $ ${totalConIva.toFixed(4)}
                </td>
                <td class="align-middle">
                    <span>${$('#necesidad option:selected').text()}</span>
                    <input type="hidden" name="necesidad[${numeroFila - 1}]" value="${$('#necesidad option:selected').text()}">
                </td>
                <td class="align-middle table-actions">
                        <div class="action-buttons">
                            <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto"
                                id="">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </td>
                </tr>
        `;

        $('tbody').append(nuevaFila);


        inicializarPlugins();
        calcularTotalFilasYGeneral();
        clearInputs();
        $('#tr-default').hide();

        Toast.fire({
            icon: 'success',
            title: 'Producto agregado correctamente.',
        });
    });


    $('select[name=proyecto]').on('change', function () {
        var proyectoId = $(this).val();
        // Limpiar el select de subproyectos
        $('select[name=subproyecto]').empty();
        if (proyectoId <= 0) {
            $(".contenedor-nro-proforma").html('');
            $(".contenedor-nro-proforma").removeClass('col-md-4 col-12');
            return;
        }
        $.ajax({
            url: base_url + '/administrativo/subproyectos-por-proyecto',
            headers: { 'X-CSRF-TOKEN': csrf },
            method: 'POST',
            data: { 'proyecto_id': proyectoId },
            beforeSend: function () {
                $('#loading').addClass('show');
                $('.input_errors').remove();
            },
            success: function (response) {
                var subproyectos = response.subproyectos;
                var subproyectoSelect = $('select[name=subproyecto]');
                $.each(subproyectos, function (id, nombre) {
                    subproyectoSelect.append($('<option>', { value: id, text: nombre }));
                });
                // Inicializar Select2 después de agregar las opciones
                subproyectoSelect.trigger('change');
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

    $('select[name=subproyecto]').on('change', function () {
        var subproyectoId = $(this).val();
        if (subproyectoId) {
            $(".contenedor-nro-proforma").addClass('col-md-4 col-12').html(`
                <div class="form-group">
                    <label for="nro_proforma">Nro. Proforma *</label>
                    <input type="text" name="nro_proforma" id="nro_proforma" class="form-control" placeholder="Ingrese Nro. de Proforma" >
                </div>
            `);
        } else {
            $(".contenedor-nro-proforma").html('');
            $(".contenedor-nro-proforma").removeClass('col-md-4 col-12');
        }
    });


    function calcularTotal() {
        let subtotal = 0;

        // Iterar por cada fila del tbody
        $('.elementos-agregados').each(function () {
            let totalText = $(this).find('.total_unitario').text().replace(/[^0-9.,]/g, ''); // Extraer números y coma/decimal
            let total = limpiarValores(totalText) || 0; // Reemplazar la coma decimal por un punto y convertir a número
            // Sumar al subtotal
            subtotal += parseFloat(total);
        });
        // Actualizar el total general
        $('#total-general').text(formatearUSD(subtotal));
    }

});