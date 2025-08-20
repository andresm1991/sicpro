$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('#agregar-producto').on('click', function () {
        let producto = $('#producto').val();
        let cantidad = $('#cantidad').val();
        let valorUnitario = $('#valor_unitario').val();
        let iva = $('#iva').val();
        let unidadMedida = $('#unidad_medida').val();
        let necesidad = $('#necesidad').val();
        let tipoAdquisicion = $('#tipo_adquisicion').val();
        var valid = true;
        var inventario = '';


        $('#producto, #cantidad, #valor_unitario, #iva, #unidad_medida, #necesidad').removeClass('error-border');
        $('.select2-tag').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        if (producto == "") {
            var valid = false;
            $('#producto').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('#producto').parent().append('<span class="error-message">Seleccione el producto.</span>');
        }
        if (unidadMedida == "") {
            var valid = false;
            $('#unidad_medida').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('#unidad_medida').parent().append('<span class="error-message">Seleccione el opción.</span>');
        }

        if (cantidad == "" || cantidad == 0) {
            var valid = false;
            $('#cantidad').addClass('error-border');
            $('#cantidad').parent().append('<span class="error-message">Ingrese cantidad.</span>');
        }
        if (valorUnitario == "" || valorUnitario == 0) {
            var valid = false;
            $('#valor_unitario').addClass('error-border');
            $('#valor_unitario').parent().append('<span class="error-message">Ingrese valor.</span>');
        }

        if (iva == "") {
            var valid = false;
            $('#iva').addClass('error-border');
            $('#iva').parent().append('<span class="error-message">ingrese el iva.</span>');
        }

        if (necesidad == "") {
            var valid = false;
            $('#necesidad').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('#necesidad').parent().append('<span class="error-message">Seleccione o ingrese la necesidad.</span>');
        }

        if (!valid) {
            return;
        }

        // calcular el total
        let total = cantidad * valorUnitario;
        let totalIva = (total * iva) / 100;
        let totalFinal = total + totalIva;

        numeroFila = $('.elementos-agregados').length + 1;

        if (tipoAdquisicion == 'meteriales.herramientas') {
            inventario = `<td class="align-middle">
                    <div class="checkbox-wrapper-8 d-flex justify-content-center align-items-center">
                    <input type="hidden" name="inventario[${numeroFila - 1}]" value="0">

                    <input class="tgl tgl-skewed inventario" name="inventario[${numeroFila - 1}]" id="cb3-${numeroFila - 1}" type="checkbox" value="0"/>
                        <label class="tgl-btn" data-tg-off="NO" data-tg-on="SI" for="cb3-${numeroFila - 1}"></label>
                        
                    </div>
                </td>`;
        }

        // Crear una nueva fila con los datos
        var nuevaFila = `
            <tr class="elementos-agregados">
                <td>${numeroFila}</td>
                <td>
                    ${$('#producto option:selected').text()}
                    <input type="hidden" name="producto[]" value="${producto}">
                </td>
                <td>
                    ${cantidad}
                    <input type="hidden" name="cantidad[]" value="${cantidad}">
                </td>
                <td>
                    ${$('#unidad_medida option:selected').text()}
                    <input type="hidden" name="unidad_medida[]" value="${unidadMedida}">
                </td>
                <td>
                    $ ${valorUnitario}
                    <input type="hidden" name="precio[]" value="${valorUnitario}">
                </td>
                <td>
                    ${iva}
                    <input type="hidden" name="iva[]" value="${iva}">
                </td>
                <td class="total_unitario">
                   $ ${totalFinal.toFixed(4)}
                </td>
                <td>
                    <span>${necesidad}</span>
                    <input type="hidden" name="necesidad[]" value="${necesidad}">
                </td>
                ${inventario}
                <td class="align-middle table-actions">
                    <div class="action-buttons">
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto" id=""><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </td>
            </tr>
        `;

        // Agregar la nueva fila a la tabla
        $('tbody').append(nuevaFila);

        // Ocultar tr por defecto
        $('#tr-default').hide();
        // Calcular el total general
        var totalGenral = calcularTotal('.elementos-agregados');
        $('#total-general').text('$ ' + totalGenral);

        // Limpiar campos 
        clearInputs();

    });

    $('#cantidad, #valor_unitario, #iva').on('input', function () {
        let cantidad = $('#cantidad').val();
        let valorUnitario = $('#valor_unitario').val();
        let iva = $('#iva').val();

        // calcular el total
        let total = cantidad * valorUnitario;
        let totalIva = (total * iva) / 100;
        let totalFinal = total + totalIva;

        $('#total').val('$ ' + totalFinal.toFixed(4));
    });

    /**
     * Eliminar elementos de la tabal y recalcular el total
     */
    $(document).on('click', ".eliminar-fila-producto", function () {
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

        var totalGeneral = calcularTotal('.elementos-agregados');
        $('#total-general').text('$ ' + totalGeneral);
    });



    // Evento submit del formulario para validar los campos
    $('#form_pedido').on('submit', function (event) {
        $('#proveedor').removeClass('error-border');
        $('#tipo-adquisicion').removeClass('error-border');
        $('.select2-basic-single').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores
        let isValid = true; // Variable para rastrear si el formulario es válido

        // Validar el campo "numero"
        let proveedor = $('#proveedor').val();
        if (!proveedor || proveedor.trim() === '') {
            isValid = false;
            $('#proveedor').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('#proveedor').parent().append('<span class="error-message">Seleccione proveedor.</span>');
        }

        // Validar el campo "fecha"
        let forma_pago = $('#forma-pago').is(':checked');
        if (!forma_pago) {
            isValid = false;
            $('<span>', {
                text: 'Seleccione forma de pago.',
                class: 'error-message'
            }).insertAfter($('#forma-pago').closest('.select_wrapper'));
        }

        // Validar el campo "total"
        let totalGeneral = $('#total-general').text().replace(/[$.]/g, '').trim(); // Eliminar el símbolo de dólar y espacios

        if (totalGeneral === '' || totalGeneral <= 0) {
            isValid = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se ha agregado ningún producto.',
                confirmButtonText: 'Aceptar'
            });
        }

        if ($('#tipo-adquisicion').length) {
            // El elemento existe
            let tipoAdquisicion = $('#tipo-adquisicion').val();
            if (!tipoAdquisicion || tipoAdquisicion.trim() === '') {
                isValid = false;
                $('#tipo-adquisicion').next('.select2-container').find('.select2-selection').addClass('error-border');
                $('#tipo-adquisicion').parent().append('<span class="error-message">Seleccione una opción.</span>');
            }
        } else {
            // El elemento no existe
        }

        // Si alguna validación falla, evitar el envío del formulario
        if (!isValid) {
            event.preventDefault();
        }
    });

    /**
     * Eliminar adquisicion
     * @param id
     */
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
                    url: url + '/eliminar/' + adquisicionId,
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (data) {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message,
                            });

                            $("#table-list-pedidos #" + adquisicionId).remove();

                            if ($('#table-list-pedidos tbody').children().length == 0) {
                                $('#table-list-pedidos tbody').html('<tr>' +
                                    '<td colspan = "10" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
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

    /**
     * Buscar adquisicion
     * @param buscar
     */
    $(document).on('keyup', '#buscar-adquisicion', function () {
        let buscar = $(this).val();
        let tipoAdquisicion = $(this).data('tipo');

        $.ajax({
            url: url + '/buscar',
            type: 'GET',
            data: { text: buscar },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                $('#table-list-pedidos tbody').html(response);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        });
    });

    function clearInputs() {
        $('#producto').val(null).trigger('change');
        $('#cantidad').val(null);
        $('#valor_unitario').val(null);
        $('#iva').val(null);
        $('#unidad_medida').val(null).trigger('change');
        $('#necesidad').val(null).trigger('change');
        $('#total').val(null);
    }
});