$(function () {
    $(":input").inputmask();
    var csrf = $('meta[name="csrf-token"]').attr('content');

    /**
     * Cargar la categoria cuando se seleciona al proveedor
     */
    $('.select2-basic-single').on('change', function (e) {
        // Obtén el valor seleccionado
        var selected_value = $(this).val();

        $.ajax({
            url: 'proveedor-articulos',
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
                }

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    // Evento al hacer clic en el botón para clonar
    $('#add-personal').on('click', function () {
        var personal = $('#personal option:selected').val();
        var categoria = $('#categoria option:selected').val();
        var jornada = $('#jornada option:selected').val();
        var valor = $('input:text[name=valor]').val();
        var adicional = $('input:text[name=adicional]').val();
        var descuento = $('input:text[name=descuento]').val();
        var detalle_adicional = $('input:text[name=detalle_adicional]').val();
        var detalle_descuento = $('input:text[name=detalle_descuento]').val();


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
        numeroFila = $('.elementos-agregados').length;
        // Mostrar el mensaje de que no hay elementos si no hay filas
        if (numeroFila == 0) {
            $('#tr-default').show();
        }
    });
});