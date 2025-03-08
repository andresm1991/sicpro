import { getFormData } from './helpers.js';


$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $("#agregar-item").on("click", function () {
        var item = $('select[name=descripcion]').val();
        var valor = $('input[name=valor]').val();

        if (item == "" || valor == "") {
            alert("Debe llenar todos los campos");
            return;
        }

        // Contar el número de filas actuales para generar el índice
        var rowCount = $(".elementos-agregados").length + 1; // Contar las filas existentes

        // Crear la nueva fila con un botón de eliminación
        var fila = `
       <tr class="elementos-agregados">
           <td class="index">${rowCount}</td>
           <td>${item}</td>
           <td>${valor}</td>
           <td class="table-actions">
               <button class="btn btn-danger btn-sm eliminar-fila">
                   <i class="fas fa-trash"></i>
               </button>
           </td>
           <input type="hidden" name="items[${rowCount - 1}][descripcion]" value="${item}">
           <input type="hidden" name="items[${rowCount - 1}][valor]" value="${valor}">
       </tr>
   `;

        // Agregar la fila al tbody
        $("#tabla_items tbody").append(fila);

        $('#tr-default').hide();

        calcularTotal();
        // Limpiar los campos de entrada
        $('input[name=descripcion]').val("");
        $('input[name=valor]').val("");

    });

    // Eliminar una fila y actualizar los índices
    $(document).on("click", ".eliminar-fila", function () {
        // Eliminar la fila correspondiente
        $(this).closest("tr").remove();

        // Actualizar los índices de las filas restantes
        $('.elementos-agregados').each(function (index) {
            $(this).find('td:first').text(index + 1);
        });

        var numeroFila = $('.elementos-agregados').length;
        // Mostrar el mensaje de que no hay elementos si no hay filas
        if (numeroFila == 0) {
            $('#tr-default').show();
        }

        calcularTotal();
    });

    $("#guardar").on("click", function () {
        var form = $("#form_resumen_pagos_semanales");
        var data = getFormData(form);

        $.ajax({
            url: 'resumen-pagos-semanales/guardar',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#pagoSemanalModal #modal-overlay').show();
            },
            success: function (response) {

                Swal.fire({
                    icon: response.success ? "success" : "error",
                    text: response.message,
                    confirmButtonText: 'Aceptar',
                }).then((result) => {
                    if (response.success) {
                        location.reload();
                    }
                });


                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            complete: function () {
                $('#pagoSemanalModal #modal-overlay').hide();
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            switch (jqXHR.status) {
                case 422: // ERROR INPUT VALIDATE

                    break;

                case 419: // ERROR EXPIRATE SESSION
                    window.location = '/';
                    break;

                default:
                    var errors = JSON.parse(jqXHR.responseText);
                    Swal.fire(
                        'Ups.!',
                        'Algo salió mal, por favor vuelva a intentarlo.',
                        'error'
                    )
                    console.log(errors)
            }
        });
    });

    $(document).on("click", ".eliminar-resumen", function () {
        var id = $(this).attr('id');
        var $this = $(this);

        // Confirmar la eliminación
        Swal.fire({
            title: '¿Estás seguro?',
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
                    url: `resumen-pagos-semanales/eliminar/${id}`,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (data) {
                        if (data.success) {
                            // Eliminar el elemento del DOM
                            $("#" + id).remove();

                            if ($('#resumen_pagos_table tbody').children().length == 0) {
                                $('#resumen_pagos_table tbody').html('<tr>' +
                                    '<td colspan = "5" class="text-center text-danger">No se encontraron datos para mostrar.</td>' +
                                    '</tr>');
                            }

                        }

                        Toast.fire({
                            title: data.success ? 'Success!' : 'Error!',
                            text: data.message,
                            icon: data.success ? 'success' : 'error',

                        });

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

    $(document).on('click', '.editar-resumen', function () {
        var id = $(this).attr('id');
        $('input:hidden[name=resumen_id]').val(id);

        $.ajax({
            url: `resumen-pagos-semanales/editar/${id}`,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#pagoSemanalModal #modal-overlay').show();
            },
            success: function (response) {
                $("#fecha_registro").text(response.resumen_pago.fecha);
                if (response.resumen_pago.estado.descripcion == 'Aprobado') {
                    $('input:checkbox[name=completado]').prop('checked', true);
                }

                $.each(response.resumen_pago.detalle_resumen_pago_semanal, function (index, item) {

                    // Contar el número de filas actuales para generar el índice
                    var rowCount = $(".elementos-agregados").length + 1; // Contar las filas existentes

                    // Crear la nueva fila con un botón de eliminación
                    var fila = `
                            <tr class="elementos-agregados">
                                <td class="index">${rowCount}</td>
                                <td>${item.descripcion}</td>
                                <td>${item.monto}</td>
                                <td class="table-actions">
                                    <button class="btn btn-danger btn-sm eliminar-fila">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                <input type="hidden" name="items[${rowCount - 1}][descripcion]" value="${item.descripcion}">
                                <input type="hidden" name="items[${rowCount - 1}][valor]" value="${item.monto}">
                            </tr>
                        `;

                    // Agregar la fila al tbody
                    $("#tabla_items tbody").append(fila);
                });

                $('#tr-default').hide();

                calcularTotal();
            },
            complete: function () {
                $('#pagoSemanalModal #modal-overlay').hide();
            },
        }).fail(function () {
            Swal.fire(
                'Error Inesperado!',
                'No se pudo realizar la acción de eliminado, comuníquese con el administrador del sistema.',
                'error'
            )
        });
    });

    $('#pagoSemanalModal').on('show.bs.modal', function (e) {
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


    $('#pagoSemanalModal').on('hidden.bs.modal', function (e) {
        $('input:hidden[name=resumen_id]').val('');
        // Limpiar los campos de entrada
        $('input[name=descripcion]').val("");
        $('input[name=valor]').val("");
        $('input:checkbox[name=completado]').prop('checked', false);

        // Limpiar la tabla de items
        $(".elementos-agregados").remove();

        // Mostrar el total general
        $("#total_general").val('0.0000'); // Redondear a 2 decimales

        // Mostrar el mensaje de que no hay elementos si no hay filas
        $('#tr-default').show();
    });


    $('input:text[name=resumen_pagos_search]').on('keyup', function () {
        var $value = $(this).val();
        var tipo = $(this).attr('id');

        $.ajax({
            url: 'resumen-pagos-semanales/buscar-resumen-pagos',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'text': $value, 'tipo': tipo },
            beforeSend: function () {
            },
            success: function (data) {
                if (tipo == 'pendientes') {
                    $('#resumen_pagos_pendientes_table tbody').html(data);
                } else {
                    $('#resumen_pagos_completos_table tbody').html(data);
                }

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    function calcularTotal() {
        let total = 0;

        // Recorrer todas las filas con la clase .elementos-agregados
        $("tr.elementos-agregados").each(function () {
            // Obtener el valor del campo oculto 'valor'
            let valorFormateado = $(this).find("input[name*='[valor]']").val();

            // Limpiar el valor: quitar "$", ",", y otros caracteres no numéricos
            let valorNumerico = parseFloat(valorFormateado.replace(/[^0-9.-]/g, ''));

            // Sumar el valor limpio al total
            if (!isNaN(valorNumerico)) {
                total += valorNumerico;
            }
        });

        // Mostrar el total general
        $("#total_general").val(total.toFixed(4)); // Redondear a 2 decimales
    }
});