$(function(){
    var csrf = $('meta[name="csrf-token"]').attr('content');

    /**
     * Cargar la categoria de cada proveedor cuando se selecciona
     */
    $('#proveedor').on('change', function (e) {
        // Remueve la clase 'error-border' del contenedor generado por select2
        $(this).closest('.form-group').find('.select2-selection').removeClass('error-border');

        // Elimina solo el mensaje de error asociado con este select2
        $(this).closest('.form-group').find('.error-message').remove();

        // Obtén el valor seleccionado
        var selected_value = $(this).val();
        $.ajax({
            url: '/articulos-proveedor',
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

    $('#agregar-producto').on('click', function () {
        $('#producto, #unidad-medida, input[name=cantidad], input[name=precio-unitario]').removeClass('error-border');
        $('.select2-selection').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        var producto_id = $('#producto').val();
        var producto_name = $('#producto option:selected').text();
        var medida_id = $('#unidad-medida').val();
        var medida_name = $('#unidad-medida option:selected').text();
        var cantidad = $('input[name=cantidad]').val();
        var precio_unitario = $('input[name=precio-unitario]').val();
        var valid = true;

        if (!producto_id) {
            valid = false;
            $('#producto').next('.select2-container').find('.select2-selection').addClass('error-border');
            $('#producto').parent().append('<span class="error-message">Seleccione el producto.</span>');
        }

        if (!medida_id) {
            valid = false;
            $('#unidad-medida').next('.select2-container').find('.select2-selection').addClass('error-border'); 
            $('#unidad-medida').parent().append('<span class="error-message">Seleccione la unidad de medida.</span>');
        }

        if (!cantidad) {
            valid = false;
            $('input:text[name=cantidad]').addClass('error-border');
            $('input:text[name=cantidad]').parent().append('<span class="error-message">Ingrese cantidad.</span>');
        }

        if (!precio_unitario) {
            valid = false;
            $('input:text[name=precio-unitario]').addClass('error-border');
            $('input:text[name=precio-unitario]').parent().append('<span class="error-message">Ingrese precio.</span>');
        }

        if (!valid) {
            return;
        }

        var numeroFila = $('.elementos-agregados').length + 1;
        var total = parseFloat(cantidad) * parseFloat(precio_unitario);
        var formattedNumber = total.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        var nuevaFila = `
            <tr class="elementos-agregados">
                <td>${numeroFila}</td>
                <td>
                    ${producto_name}
                    <input type="hidden" name="productos[]" value="${producto_id}">
                </td>
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
                <td class="edit-item">
                    <span>${medida_name}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2" name="unidad_medida[]"
                            value="${medida_id}">
                        <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </td>
                <td class="edit-item">
                    <span>$ ${precio_unitario}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2" name="precio_unitario[]"
                            value="${precio_unitario}">
                        <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </td>
                <td>$ ${formattedNumber}</td>
                <td class="align-middle table-actions">
                    <div class="action-buttons">
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto" id=""><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </td>
            </tr>
        `;
        $('tbody').append(nuevaFila);
        $('#tr-default').hide();
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
        var numeroFila = $('.elementos-agregados').length;
        // Mostrar el mensaje de que no hay elementos si no hay filas
        if (numeroFila == 0) {
            $('#tr-default').show();
        }
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
        $('#producto').val(null).trigger('change');
        $('#unidad-medida').val(null).trigger('change');
        $('input[name=cantidad]').val("");
        $('input[name=precio-unitario]').val("");

    }
});