$(function () {
    var numeroFila = 0;

    $('select').select2({
        width: '100%',
        allowClear: false, // Permite limpiar la selección
        tags: true, // Permite agregar nuevas opciones
        placeholder: function () {
            $(this).data('placeholder');
        },
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
     * Eliminar pedido
     */
    $(document).on('click', '.eliminar-pedido', function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');
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
                    url: 'eliminar-adquisicion/' + id,
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
                                    '<td colspan = "7" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
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
     * Buscar Pedido
     * @param String
     * return JSON
    */
    $('input:text[name=adquisicion_search]').on('keyup', function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var $value = $(this).val();

        $.ajax({
            url: url + '/buscar-pedido',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value },
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


    /**
     * Agregar elementos a la tabla de pedidos
     */
    $('#add-producto-adquisicion').on('click', function () {
        var producto_id = $('#productos').val();
        var producto = $('#productos option:selected').text();
        var cantidad = $('#cantidad').val();
        var necesidad = $('#necesidad option:selected').text();
        var tipo_etapa = $('input:hidden[name=slug_adquisicion]').val();
        var td_servicios = '';
        var td_inventario = '';

        if (tipo_etapa == 'SERVICIOS') {
            var unidad_medida_id = $('#unidad_medida').val();
            var unidad_medida_text = $('#unidad_medida option:selected').text();
            var precio = $('input:text[name=precio_unitario]').val();

            td_servicios = `<td>
                ${unidad_medida_text}
                <input type="hidden" name="unidad_meddia[]" value="${unidad_medida_id}">
            </td>
            <td class="edit-item">
                    <span>${precio}</span>
                    <div class="d-flex align-items-center hidden">
                        <input type="text" class="form-control mr-2 input-double" name="precio[]"
                            value="${precio}">
                        <button type="button" class="btn btn-outline-dark btn-sm mr-1 aceptar"><i
                                class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn btn-outline-dark btn-sm cancelar"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </td>
            `;
        }


        if (producto_id == '' || cantidad == '' || necesidad == '' || (typeof unidad_medida_id != 'undefined' && unidad_medida_id == '') || (typeof precio != 'undefined' && precio == '')) {
            Toast.fire({
                icon: 'error',
                title: 'Complete los campos para agregar productos a su pedido.',
            });
            return;
        }

        numeroFila = $('.elementos-agregados').length + 1;

        if (tipo_etapa == 'METERIALES.HERRAMIENTAS') {
            td_inventario = `<td class="align-middle">
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
                ${td_servicios}
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
                ${td_inventario}
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

    // Obtener la fecha seleccionada cuando el usuario elige una fecha
    $('#fecha').datepicker().on('changeDate', function (e) {
        var fechaSeleccionada = $(this).datepicker('getFormattedDate');
        $('input[name=fecha]').val(fechaSeleccionada);
    });

    // Maneja el evento change de select2
    $('#productos').on('change', function () {
        var selected = $('#productos option:selected').text();

        if (selected === 'gasolina para camioneta') {
            // Muestra la columna si es "Gasolina para Camioneta"
            $('th.col-gasolina, td.col-gasolina').show();
        } else if (selected != '' && selected != 'gasolina para camioneta') {
            // Oculta la columna para cualquier otra opción
            $('th.col-gasolina, td.col-gasolina').hide();

        }
    });


    /**
     * Funciones
     */
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
});