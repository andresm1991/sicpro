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
            url: base_url + '/articulos-proveedor',
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

    $('#agregar-producto').on('click', function (e) {
        e.preventDefault();

        const camposAValidar = [
            { selector: '#producto', mensaje: 'Seleccione el producto.' },
            { selector: '#unidad-medida', mensaje: 'Seleccione la opción.' },
            { selector: '#cantidad', mensaje: 'Ingrese cantidad.' },
            { selector: '#precio-unitario', mensaje: 'Ingrese valor.' },
            { selector: '#iva', mensaje: 'Ingrese el IVA.' },
        ];

        // Validar campos
        if (!validarCampos(camposAValidar)) {
            return; // Detener si hay errores
        }

        var producto = $('#producto').val();
        var cantidad = $('#cantidad').val();
        var valorUnitario = $('#precio-unitario').val();
        var unidadMedida = $('#unidad-medida').val();
        var iva = $('#iva').val() || 0; // Valor por defecto de IVA es 0 si no se proporciona


        numeroFila = $('.elementos-agregados').length + 1;
        var total = (cantidad * valorUnitario) + ((cantidad * valorUnitario) * (iva / 100));

        // Crear una nueva fila con los datos
        var nuevaFila = `
        <tr class="elementos-agregados">
            <td class="aling-middle">${numeroFila}</td>
            <td class="aling-middle">
                ${$('#producto option:selected').text()}
                <input type="hidden" name="producto[]" value="${producto}">
            </td>
            <td class="aling-middle">
                ${cantidad}
                <input type="hidden" name="cantidad[]" value="${cantidad}">
            </td>
            <td class="aling-middle">
                ${$('#unidad-medida option:selected').text()}
                <input type="hidden" name="unidad_medida[]" value="${unidadMedida}">
            </td>
            <td class="aling-middle">
                $ ${valorUnitario}
                <input type="hidden" name="precio[]" value="${valorUnitario}">
            </td>
            <td class="aling-middle">
                ${iva}
                <input type="hidden" name="iva[]" value="${iva}">
            </td>
            <td class="aling-middle total_unitario">
               $ ${total.toFixed(4)}
            </td>
            
            <td class="align-middle table-actions">
                <div class="action-buttons">
                    <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto" id=""><i class="fa-solid fa-trash-can"></i></a>
                </div>
            </td>
        </tr>
    `;
        $('tbody').append(nuevaFila);

        var totalGenral = calcularTotal('.elementos-agregados');
        $('#total-general').text('$' + totalGenral);

        // Ocultar tr por defecto
        $('#tr-default').hide();
        limpiarCampos(camposAValidar);
    });

    $(document).on('click', '.eliminar-fila-producto', function () {
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
    $('#form_contratista').on('submit', function (event) {

        let isValid = true;

        const camposAValidar = [
            { selector: '#proveedor', mensaje: 'Seleccione proveedor.' },
            { selector: '#categoria', mensaje: 'Seleccione categoria.' },
        ];

        // Validar campos
        if (!validarCampos(camposAValidar)) {
            isValid = false;
        } else {
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
        }

        // Si alguna validación falla, evitar el envío del formulario
        if (!isValid) {
            event.preventDefault();
        }
    });


});