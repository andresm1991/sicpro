$(function () {
    var numeroFila = 0;

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

        numeroFila++;
        // Crear una nueva fila con los datos
        var nuevaFila = `
            <tr class="elemtos-agregados">
                <td>${numeroFila}</td>
                <td>${producto}</td>
                <td>${cantidad}</td>
                <td>${necesidad}</td>
                <td class="align-middle table-actions">
                    <div class="action-buttons">
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto" id=""><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </td>
                <input type="hidden" name="productos[]" value="${producto_id}">
                <input type="hidden" name="cantidad[]" value="${cantidad}">
                <input type="hidden" name="necesidad[]" value="${necesidad}">
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
        $('.elemtos-agregados').each(function (index) {
            $(this).find('td:first').text(index + 1);
        });
        numeroFila = $('.elemtos-agregados').length;

        // Mostrar el mensaje de que no hay elementos si no hay filas
        if ($('.elemtos-agregados').length == 0) {
            $('#tr-default').show();
        }

        // habilitar la opción seleccionada
        //$('#productos option[value="' + producto_id + '"]').prop('disabled', false);
        //$("#productos").selectpicker("refresh");
    });

    /**
     * Funciones
     */
    function clearInputs() {
        $('#productos').val(null).trigger('change');
        $('#cantidad').val("");
        $('#necesidad').val("");

    }

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
});