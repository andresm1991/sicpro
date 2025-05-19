$(function () {

    var csrf = $('meta[name="csrf-token"]').attr('content');

    /// Evento de ingreso total ganado
    $('#sueldo, #horas_extras').on('keyup', function () {
        let sueldo = parsePrecio($('#sueldo').val());
        let horasExtras = parsePrecio($('#horas_extras').val());
        let totalGanado = sueldo + horasExtras;

        $("#total_ganado").text("$ " + totalGanado.toFixed(4));
        $("#total_ingresos").text("$ " + totalGanado.toFixed(4));
        cacularTotalRecibir();
    });

    /// Evento de ingreso total ingresos
    $('#fondos, #decimo_tercero, #decimo_cuarto').on('keyup', function () {
        let totalGanado = parsePrecio($("#total_ganado").text());
        let fondos = parsePrecio($('#fondos').val());
        let decimoTercero = parsePrecio($('#decimo_tercero').val());
        let decimoCuarto = parsePrecio($('#decimo_cuarto').val());
        let totalIngresos = totalGanado + fondos + decimoTercero + decimoCuarto;

        $("#total_ingresos").text("$ " + totalIngresos.toFixed(4));
        cacularTotalRecibir();
    });


    /// Evento de ingreso total descuentos
    $('#iess, #atrasos_faltas, #anticipos, #prestamo_iess, #quincena, #prestamo_jp').on('keyup', function () {
        let iess = parsePrecio($('#iess').val());
        let antcipos = parsePrecio($('#anticipos').val());
        let prestamoIess = parsePrecio($('#prestamo_iess').val());
        let quincena = parsePrecio($('#quincena').val());
        let prestamoJp = parsePrecio($('#prestamo_jp').val());
        let atrasosFaltas = parsePrecio($('#atrasos_faltas').val());
        let totalDescuentos = iess + antcipos + prestamoIess + quincena + prestamoJp + atrasosFaltas;

        $("#total_descuentos").text("$ " + totalDescuentos.toFixed(4));
        cacularTotalRecibir();
    });

    $("#agregar-personal").on("click", function () {

        const camposAValidar = [
            { selector: '#fecha_desde', mensaje: 'Seleccione la fecha desde.' },
            { selector: '#fecha_hasta', mensaje: 'Seleccione la fecha hasta' },
            { selector: '#proveedor', mensaje: 'Seleccione el proveedor.' },
            { selector: '#sueldo', mensaje: 'Ingrese el sueldo.' },
            { selector: '#iess', mensaje: 'Ingrese el iess.' },
        ];

        // Validar campos
        if (!validarCampos(camposAValidar)) {
            return; // Detener si hay errores
        }


        let proveedorId = $("#proveedor").val();
        let proveedorText = $("#proveedor option:selected").text();
        let sueldo = parsePrecio($("#sueldo").val());
        let horasExtras = parsePrecio($("#horas_extras").val()) || 0;
        let totalGanado = parsePrecio($("#total_ganado").text());
        let fondos = parsePrecio($("#fondos").val()) || 0;
        let decimoTercero = parsePrecio($("#decimo_tercero").val()) || 0;
        let decimoCuarto = parsePrecio($("#decimo_cuarto").val()) || 0;
        let totalIngresos = parsePrecio($("#total_ingresos").text());
        let iess = parsePrecio($("#iess").val()) || 0;
        let atrasosFaltas = parsePrecio($('#atrasos_faltas').val()) || 0;
        let antcipos = parsePrecio($("#anticipos").val()) || 0;
        let prestamoIess = parsePrecio($("#prestamo_iess").val()) || 0;
        let quincena = parsePrecio($("#quincena").val()) || 0;
        let prestamoJp = parsePrecio($("#prestamo_jp").val()) || 0;
        let totalDescuentos = parsePrecio($("#total_descuentos").text());
        let totalRecibir = parsePrecio($("#total_recibir").text());


        var nuevaFila = `<tr class="elementos-agregados">
            <td>
                ${proveedorText}
                <input type="hidden" name="proveedor[]" value="${proveedorId}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${sueldo.toFixed(4)}</span>
                <input type="hidden" name="sueldo[]" value="${sueldo}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${horasExtras.toFixed(4)}</span>
                <input type="hidden" name="h_extras[]" value="${horasExtras}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${totalGanado.toFixed(4)}</span>
                <input type="hidden" name="total_ganado[]" value="${totalGanado}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${fondos.toFixed(4)}</span>
                <input type="hidden" name="fondos[]" value="${fondos}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${decimoTercero.toFixed(4)}</span>
                <input type="hidden" name="decimo_tercero[]" value="${decimoTercero}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${decimoCuarto.toFixed(4)}</span>
                <input type="hidden" name="decimo_cuarto[]" value="${decimoCuarto}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${totalIngresos.toFixed(4)}</span>
                <input type="hidden" name="total_ingresos[]" value="${totalIngresos}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${iess.toFixed(4)}</span>
                <input type="hidden" name="iess[]" value="${iess}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${atrasosFaltas.toFixed(4)}</span>
                <input type="hidden" name="atrasos_faltas[]" value="${atrasosFaltas}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${antcipos.toFixed(4)}</span>
                <input type="hidden" name="anticipos[]" value="${antcipos}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${prestamoIess.toFixed(4)}</span>
                <input type="hidden" name="prestamo_iess[]" value="${prestamoIess}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${quincena.toFixed(4)}</span>
                <input type="hidden" name="quincena[]" value="${quincena}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${prestamoJp.toFixed(4)}</span>
                <input type="hidden" name="prestamo_jp[]" value="${prestamoJp}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${totalDescuentos.toFixed(4)}</span>
                <input type="hidden" name="total_descuentos[]" value="${totalDescuentos}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${totalRecibir.toFixed(4)}</span>
                <input type="hidden" name="total_recibir[]" value="${totalRecibir}">
            </td>
            <td>
                <button type="button" class="btn btn-dark btn-remove-personal"> - </button>
            </td>
        </tr>`;


        $('tbody').append(nuevaFila);
        $('#tr-default').hide();
    });

    $(document).on('click', '.btn-remove-personal', function () {
        $(this).closest('tr').remove();
        var numeroFila = $('.elementos-agregados').length;
        // Mostrar el mensaje de que no hay elementos si no hay filas
        if (numeroFila == 0) {
            $('#tr-default').show();
        }
    });

    $(document).on('click', ".edit-item", function () {
        var $td = $(this);
        var $span = $td.find('span');
        var $input = $td.find('input');
        var valorActual = $input.val();

        Swal.fire({
            title: 'Editar valor',
            input: 'text',
            inputValue: valorActual,
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'El valor no puede estar vacío';
                }
            },
            didOpen: () => {
                const input = Swal.getInput();
                input.classList.add('money');
                input.placeholder = 'Ingrese el nuevo valor';
                // Inicializa maskMoney en el input de SweetAlert
                $(input).maskMoney({ prefix: '$ ', allowNegative: true, affixesStay: false, precision: 4 });
                $(input).maskMoney('mask'); // Opcional: para aplicar el formato inicial
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $span.text('$ ' + result.value);
                $input.val(result.value);
            }
        });
    });

    $(document).on('click', '.eliminar-mano-obra', function () {
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
                    url: manObraUrl + '/eliminar-mano-obra/' + id,
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
                                    '<td colspan = "4" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
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

    function cacularTotalRecibir() {
        let totalIngresos = parsePrecio($("#total_ingresos").text()) || 0;
        let totalDescuentos = parsePrecio($("#total_descuentos").text()) || 0;
        let totalRecibir = totalIngresos - totalDescuentos;
        $("#total_recibir").text("$ " + totalRecibir.toFixed(4));
    }

});