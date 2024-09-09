$(function () {
    var numeroFila = 0;

    $('#productos').select2({
        allowClear: false, // Permite limpiar la selección
        tags: true, // Permite agregar nuevas opciones
        placeholder: "Selecciona o agrega un producto",
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

    /**
     * Agregar elementos a la tabla de pedidos
     */
    $('#add-producto-adquisicion').on('click', function () {
        var producto_id = $('#productos').val();
        var producto = $('#productos option:selected').text();
        var cantidad = $('#cantidad').val();
        var necesidad = $('#necesidad').val();

        if (producto_id == '' || cantidad == '' || necesidad == '') {
            Toast.fire({
                icon: 'error',
                title: 'Complete los campos para agregar productos a su pedido.',
            });
            return;
        }

        numeroFila = $('.elementos-agregados').length + 1;
        // Crear una nueva fila con los datos
        var nuevaFila = `
            <tr class="elementos-agregados">
                <td>${numeroFila}</td>
                <td>${producto}</td>
                <td class="edit-item">
                    <span>${cantidad}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2 solo-numeros" name="cantidad[]"
                            value="${cantidad}">
                        <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </td>
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

    /**
     * Funciones
     */
    function clearInputs() {
        $('#productos').val(null).trigger('change');
        $('#cantidad').val("");
        $('#necesidad').val("");

    }
});