$(function () {

    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#fondos').attr('readonly', true);
    $('#decimo_tercero').attr('readonly', true);
    $('#decimo_cuarto').attr('readonly', true);
    $('#iess').attr('readonly', true);

    var pFondos = 0.0833;
    var pIESS = 0.0945;
    var dTerceroYCuarto = 12;
    var basico = 470;



    $('#tipo_plantilla').on('change', function () {
        let tipoPlantilla = $(this).val();

        if (tipoPlantilla == 'ASOCIACION') {
            $('#fondos').attr('readonly', false);
            $('#decimo_tercero').attr('readonly', false);
            $('#decimo_cuarto').attr('readonly', false);
            $('#iess').attr('readonly', false);
            $('#fondos').val('');
            $('#decimo_tercero').val('');
            $('#decimo_cuarto').val('');
            $('#iess').val('');
        } else {
            $('#fondos').attr('readonly', true);
            $('#decimo_tercero').attr('readonly', true);
            $('#decimo_cuarto').attr('readonly', true);
            $('#iess').attr('readonly', true);
        }
    });
    /// Evento de ingreso total ganado
    $('#sueldo, #horas_extras').on('keyup', function () {
        let sueldo = parsePrecio($('#sueldo').val());
        let horasExtras = parsePrecio($('#horas_extras').val());
        let totalGanado = sueldo + horasExtras;
        let iess = totalGanado * pIESS;
        let totalIngresos = totalGanado;


        if ($('#tipo_plantilla').val() == 'JPLIMPIEZA') {
            let fondos = sueldo * pFondos;
            let decimoTercero = totalGanado / dTerceroYCuarto;
            let decimoCuarto = basico / dTerceroYCuarto;

            totalIngresos += fondos + decimoTercero + decimoCuarto;

            $("#fondos").val(fondos.toFixed(2));
            $("#decimo_tercero").val(decimoTercero.toFixed(2));
            $("#decimo_cuarto").val(decimoCuarto.toFixed(2));
        }

        $("#iess").val(iess.toFixed(2));
        $("#total_ganado").text("$ " + totalGanado.toFixed(2));
        $("#total_ingresos").text("$ " + totalIngresos.toFixed(2));
        cacularTotalRecibir();
    });

    /// Evento de ingreso total ingresos
    $('#fondos, #decimo_tercero, #decimo_cuarto').on('keyup', function () {
        let totalGanado = parsePrecio($("#total_ganado").text());
        let fondos = parsePrecio($('#fondos').val());
        let decimoTercero = parsePrecio($('#decimo_tercero').val());
        let decimoCuarto = parsePrecio($('#decimo_cuarto').val());
        let totalIngresos = totalGanado + fondos + decimoTercero + decimoCuarto;

        $("#total_ingresos").text("$ " + totalIngresos.toFixed(2));
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

        $("#total_descuentos").text("$ " + totalDescuentos.toFixed(2));
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
        let aportePatronal = parsePrecio($("#aporte_patronal").val()) || 0;


        var nuevaFila = `<tr class="elementos-agregados">
            <td>
                ${proveedorText}
                <input type="hidden" name="proveedor[]" value="${proveedorId}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${sueldo.toFixed(2)}</span>
                <input type="hidden" name="sueldo[]" value="${sueldo}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${horasExtras.toFixed(2)}</span>
                <input type="hidden" name="h_extras[]" value="${horasExtras}">
            </td>
            <td>
                <span>$ ${totalGanado.toFixed(2)}</span>
                <input type="hidden" name="total_ganado[]" value="${totalGanado}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${fondos.toFixed(2)}</span>
                <input type="hidden" name="fondos[]" value="${fondos}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${decimoTercero.toFixed(2)}</span>
                <input type="hidden" name="decimo_tercero[]" value="${decimoTercero}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${decimoCuarto.toFixed(2)}</span>
                <input type="hidden" name="decimo_cuarto[]" value="${decimoCuarto}">
            </td>
            <td>
                <span>$ ${totalIngresos.toFixed(2)}</span>
                <input type="hidden" name="total_ingresos[]" value="${totalIngresos}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${iess.toFixed(2)}</span>
                <input type="hidden" name="iess[]" value="${iess}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${aportePatronal.toFixed(2)}</span>
                <input type="hidden" name="aporte_patronal[]" value="${aportePatronal}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${atrasosFaltas.toFixed(2)}</span>
                <input type="hidden" name="atrasos_faltas[]" value="${atrasosFaltas}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${antcipos.toFixed(2)}</span>
                <input type="hidden" name="anticipos[]" value="${antcipos}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${prestamoIess.toFixed(2)}</span>
                <input type="hidden" name="prestamo_iess[]" value="${prestamoIess}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${quincena.toFixed(2)}</span>
                <input type="hidden" name="quincena[]" value="${quincena}">
            </td>
            <td class="edit-item" style="cursor: pointer;">
                <span>$ ${prestamoJp.toFixed(2)}</span>
                <input type="hidden" name="prestamo_jp[]" value="${prestamoJp}">
            </td>
            <td>
                <span>$ ${totalDescuentos.toFixed(2)}</span>
                <input type="hidden" name="total_descuentos[]" value="${totalDescuentos}">
            </td>
            <td>
                <span>$ ${totalRecibir.toFixed(2)}</span>
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
        var valorActual = parsePrecio($input.val());
        var $tr = $td.closest('tr'); // Obtener la fila actual
        var colName = $input.attr('name').replace('[]', ''); // Obtener el nombre del campo

        var tipoPlantilla = $("#tipo_plantilla").val();

        if (tipoPlantilla == 'JPLIMPIEZA' && (colName == 'fondos' || colName == 'decimo_tercero' || colName == 'decimo_cuarto' || colName == 'iess')) {
            return;
        }

        Swal.fire({
            title: 'Editar valor',
            input: 'text',
            inputValue: valorActual,
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                const input = Swal.getInput();
                input.classList.add('money');
                input.placeholder = 'Ingrese el nuevo valor';
                $(input).maskMoney({ prefix: '$ ', allowNegative: true, affixesStay: false, precision: 2 });
                $(input).maskMoney('mask');
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var value = parsePrecio(result.value) || 0.00
                // Actualizar el valor en el campo
                $span.text('$ ' + value.toFixed(2));
                $input.val(value);

                // Recalcular valores dependientes
                recalculateDependentValues($tr, colName);
            }
        });
    });

    /**
     * Funcion que recalcula los valores de las filas de la tabla
     * cuando cambia un valor
     */
    function recalculateDependentValues($tr, changedField) {
        var tipoPlantilla = $("#tipo_plantilla").val();

        // Obtener todos los valores necesarios de la fila
        let sueldo = parseFloat($tr.find('input[name="sueldo[]"]').val()) || 0;
        let horasExtras = parseFloat($tr.find('input[name="h_extras[]"]').val()) || 0;
        let totalGanado = 0;
        let fondos = 0;
        let decimoTercero = 0;
        let decimoCuarto = 0;
        let totalIngresos = 0;
        let iess = 0;
        let atrasosFaltas = 0;
        let anticipos = 0;
        let prestamoIess = 0;
        let quincena = 0;
        let prestamoJP = 0;
        let totalDescuentos = 0;
        let totalRecibir = 0;


        // Calcular total ganado (sueldo + horas extras)
        if (changedField === 'sueldo' || changedField === 'h_extras') {
            totalGanado = sueldo + horasExtras;
            totalIngresos = totalGanado;


            $tr.find('input[name="total_ganado[]"]').val(totalGanado.toFixed(2));
            $tr.find('span').eq(2).text('$ ' + totalGanado.toFixed(2));

            iess = totalGanado * pIESS;
            $tr.find('input[name="iess[]"]').val(iess.toFixed(2));
            $tr.find('span').eq(7).text('$ ' + iess.toFixed(2));

            if ($("#tipo_plantilla").val() == 1) {
                // Calcular fondos (8.33% del sueldo) cuando cambia el sueldo
                fondos = sueldo * pFondos;
                decimoTercero = totalGanado / dTerceroYCuarto;
                decimoCuarto = basico / dTerceroYCuarto;

                $tr.find('input[name="fondos[]"]').val(fondos.toFixed(2));
                $tr.find('span').eq(3).text('$ ' + fondos.toFixed(2));
                $tr.find('input[name="decimo_tercero[]"]').val(decimoTercero.toFixed(2));
                $tr.find('span').eq(4).text('$ ' + decimoTercero.toFixed(2));
                $tr.find('input[name="decimo_cuarto[]"]').val(decimoCuarto.toFixed(2));
                $tr.find('span').eq(5).text('$ ' + decimoCuarto.toFixed(2));

                totalIngresos += fondos + decimoTercero + decimoCuarto;
            } else {
                fondos = parseFloat($tr.find('input[name="fondos[]"]').val()) || 0;
                decimoTercero = parseFloat($tr.find('input[name="decimo_tercero[]"]').val()) || 0;
                decimoCuarto = parseFloat($tr.find('input[name="decimo_cuarto[]"]').val()) || 0;
                totalGanado = parseFloat($tr.find('input[name="total_ganado[]"]').val()) || 0;

                totalIngresos = totalGanado + fondos + decimoTercero + decimoCuarto;
            }

            $tr.find('input[name="total_ingresos[]"]').val(totalIngresos.toFixed(2));
            $tr.find('span').eq(6).text('$ ' + totalIngresos.toFixed(2)); // Índice 6 para total_ingresos
        }

        // Calcular total ingresos (total_ganado + fondos + décimos)
        if (tipoPlantilla == 'ASOCIACION' && (changedField === 'fondos' || changedField === 'decimo_tercero' || changedField === 'decimo_cuarto')) {
            fondos = parseFloat($tr.find('input[name="fondos[]"]').val()) || 0;
            decimoTercero = parseFloat($tr.find('input[name="decimo_tercero[]"]').val()) || 0;
            decimoCuarto = parseFloat($tr.find('input[name="decimo_cuarto[]"]').val()) || 0;
            totalGanado = parseFloat($tr.find('input[name="total_ganado[]"]').val()) || 0;

            totalIngresos = totalGanado + fondos + decimoTercero + decimoCuarto;

            console.log("changedField: " + changedField);
            console.log(fondos);
            console.log(totalIngresos);

            $tr.find('input[name="total_ingresos[]"]').val(totalIngresos.toFixed(2));
            $tr.find('span').eq(6).text('$ ' + totalIngresos.toFixed(2)); // Índice 6 para total_ingresos
        }

        // Obtener valores de descuentos
        iess = parseFloat($tr.find('input[name="iess[]"]').val()) || 0;
        atrasosFaltas = parseFloat($tr.find('input[name="atrasos_faltas[]"]').val()) || 0;
        anticipos = parseFloat($tr.find('input[name="anticipos[]"]').val()) || 0;
        prestamoIess = parseFloat($tr.find('input[name="prestamo_iess[]"]').val()) || 0;
        quincena = parseFloat($tr.find('input[name="quincena[]"]').val()) || 0;
        prestamoJp = parseFloat($tr.find('input[name="prestamo_jp[]"]').val()) || 0;

        totalDescuentos = iess + atrasosFaltas + anticipos + prestamoIess + quincena + prestamoJp;
        $tr.find('input[name="total_descuentos[]"]').val(totalDescuentos.toFixed(2));
        $tr.find('span').eq(13).text('$ ' + totalDescuentos.toFixed(2));

        // Calcular total a recibir (total_ingresos - total_descuentos)
        totalIngresos = parseFloat($tr.find('input[name="total_ingresos[]"]').val()) || 0;
        totalDescuentos = parseFloat($tr.find('input[name="total_descuentos[]"]').val()) || 0;
        totalRecibir = totalIngresos - totalDescuentos;

        $tr.find('input[name="total_recibir[]"]').val(totalRecibir.toFixed(2));
        $tr.find('span').eq(14).text('$ ' + totalRecibir.toFixed(2)); // Índice 14 para total_recibir
    }

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
        $("#total_recibir").text("$ " + totalRecibir.toFixed(2));
    }

});