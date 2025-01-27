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
            url: url + '/buscar-adquisicion',
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
                        url: '/pago_orden_trabajo/' + id_pago,
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
            url: '/administrativo/buscar-orden-trabajo',
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
            url: '/administrativo/buscar-mano-obra',
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

        var tipo_etapa = $('input:hidden[name=slug_adquisicion]').val();
        var td_servicios = '';
        var td_inventario = '';

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

        if (tipo_etapa == 'SERVICIOS') {
            var unidad_medida_id = $('#unidad_medida').val();
            var unidad_medida_text = $('#unidad_medida option:selected').text();
            var precio = $('input:text[name=precio_unitario]').val();

            td_servicios = `
            `;
        }

        numeroFila = $('.elementos-agregados').length + 1;

        if (tipo_etapa == 'METERIALES.HERRAMIENTAS') {
            td_inventario = ``;
        }
        // Calcular el subtotal
        let subtotal = parseFloat(cantidad) * parseFloat(valor_unitario);

        // Calcular el total con IVA
        let totalConIva = subtotal * (1 + parseFloat(iva) / 100);

        // Crear una nueva fila con los datos
        var nuevaFila = `
            <tr class="elementos-agregados">
                <td>${numeroFila}</td>
                <td>${producto}</td>
                <td class="edit-item">
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
                    <input type="hidden" name="unidad_meddia[]" value="${unidad_medida_id}">
                </td>
                <td class="edit-item">
                    <span>${valor_unitario}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2 input-double" name="precio[]"
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
                <td>${iva}%</td>
                <td>${totalConIva.toFixed(2)}</td>
                <td class="edit-item">
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
        $('#cantidad').val("");
        $('#necesidad').val(null).trigger('change');

        var tipo_etapa = $('input:hidden[name=slug_adquisicion]').val();
        if (tipo_etapa == 'SERVICIOS') {
            $('#unidad_medida').val(null).trigger('change');
            $('input:text[name=precio_unitario]').val("");
        }
    }

    $('#form_order_pedido').on('submit', function (e) {
        e.preventDefault(); // Detiene el envío por defecto
    });
});