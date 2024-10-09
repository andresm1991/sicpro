$(function () {
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
        var total = parseFloat(cantidad.replace(/,/g, '')) * parseFloat(precio_unitario.replace(/,/g, ''));
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
                <td>
                    <span>${medida_name}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="hidden" name="unidad_medida[]" value="${medida_id}">
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
     * Editar elementos de td items
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
        if($(this).closest('.edit-item').find('input').attr('name') == 'precio_unitario[]'){
            console.log(newValue)
            $(this).closest('.edit-item').find('span').text(formatoMoneda(newValue));
        }else{
            $(this).closest('.edit-item').find('span').text(newValue);
        }
        
       

        // Ocultar el div y mostrar el span nuevamente
        $(this).closest('div').addClass('hidden');
        $(this).closest('.edit-item').find('span').show();

         // Llama a la función para actualizar el total
        actualizarTotal($(this).closest('tr'));
    });

    // Botón "cancelar" para cancelar el cambio
    $(document).on('click', '.cancelar', function (event) {
        event.stopPropagation(); // Evitar que se dispare el evento del <td>

        // Ocultar el div y mostrar el span nuevamente sin hacer cambios
        $(this).closest('div').addClass('hidden');
        $(this).closest('.edit-item').find('span').show();
    });

    $(document).on('click', '.eliminar-orden-trabajo', function () {
        var $this = $(this);
        var id = $(this).attr('id');

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
                    url: '/orden-trabajo/eliminar/' + id,
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

                            $("#" + id).remove();

                            if ($('tbody').children().length == 0) {
                                $('tbody').html('<tr>' +
                                    '<td colspan = "8" class="text-center text-danger">No se encontraron datos para mostrar.</td>' +
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
     * Buscar
     * @param String
     * return JSON
    */
    $('input:text[name=orden_contratista_search]').on('keyup', function () {
        var $value = $(this).val();

        $.ajax({
            url: url + '/orden-trabajo/buscar',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'text': $value },
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

     // Obtener la fecha seleccionada cuando el usuario elige una fecha
     $('#fecha').datepicker().on('changeDate', function(e) {
        var fechaSeleccionada = $(this).datepicker('getFormattedDate');
        $('input[name=fecha]').val(fechaSeleccionada);
        // Definir opciones para el formato de fecha
        var opciones = { 
            weekday: 'long',   // Día de la semana completo
            year: 'numeric',   // Año en formato numérico
            month: 'long',     // Mes completo
            day: 'numeric'     // Día
        };

        var fecha =new Date(fechaSeleccionada+'T00:00:00');
        // Formatear la fecha
        var fechaFormateada = new Intl.DateTimeFormat('es-ES', opciones).format(fecha);
        // Eliminar la coma manualmente
        fechaFormateada = fechaFormateada.replace(',', '');

        // Función para capitalizar solo el día y el mes, dejando "de" en minúsculas
        function capitalizarDiaMes(str) {
            return str.replace(/(^\w{1}|\s\w{1})/g, function(l) { return l.toUpperCase(); })
                    .replace(/\sDe\s/g, ' de ');  // Asegurarse de que "de" se mantenga en minúsculas
        }

        // Aplicar la función
        var resultadoFinal = capitalizarDiaMes(fechaFormateada);

        $('#text-fecha').html('Fecha. '+resultadoFinal+' ');
    });

    function clearInputs() {
        $('#producto').val(null).trigger('change');
        $('#unidad-medida').val(null).trigger('change');
        $('input[name=cantidad]').val("");
        $('input[name=precio-unitario]').val("");

    }

    // Función para recalcular y actualizar el total
    function actualizarTotal($row) {
        // Obtener los valores de cantidad y precio unitario
        var cantidad = parseFloat($row.find('input[name="cantidad[]"]').val()) || 0;
        var precioUnitario = parseFloat($row.find('input[name="precio_unitario[]"]').val()) || 0;

        // Calcular el nuevo total
        var total = cantidad * precioUnitario;

        // Actualizar el valor del td de total con el nuevo valor calculado
        $row.find('td .total').text('$ ' + total.toFixed(2));
    }

    function formatoMoneda(numero) {
        // Convertir a número en caso de que sea una cadena
        numero = parseFloat(numero) || 0; // Si no es un número válido, se establece a 0
        return '$' + numero.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
});