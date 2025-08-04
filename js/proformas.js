$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var urlActual = window.location.href;
    var tipoProforma = $('#tipo-proforma').val();

    /**
     * Agregar producto a la proforma
     */
    $('#agregar-producto').on('click', function () {
        let productoId = $('#producto').val(); // Usaremos productoId para más claridad
        let productoTexto = $('#producto option:selected').text();
        let nuevaCantidad = parseFloat(tipoProforma == 'adecentamientos' ? $('#cantidad').val() : $('#area_m2').val()); // Convertir a número
        let valorUnitario = parseFloat($('#valor_unitario').val()); // Convertir a número
        var valid = true;

        const camposAValidar = [
            { selector: '#producto', mensaje: 'Seleccione producto.' },
            { selector: tipoProforma == 'adecentamientos' ? '#cantidad' : '#area_m2', mensaje: tipoProforma == 'adecentamientos' ? 'ingrese cantidad.' : 'ingrese area' },
            { selector: '#valor_unitario', mensaje: 'ingrese valor.' },
        ];
        validarCampos(camposAValidar);

        if (!validarCampos(camposAValidar)) {
            return; // Si hay un campo inválido, detenemos la ejecución
        }

        // --- LÓGICA DE VERIFICACIÓN ---
        // Buscamos si ya existe una fila para este producto
        let filaExistente = $(`tr.elementos-agregados[data-producto-id="${productoId}"]`);

        if (filaExistente.length > 0) {
            // --- SI EL PRODUCTO YA EXISTE, ACTUALIZAMOS ---

            // 1. Obtener la cantidad actual de la fila
            let celdaCantidad = filaExistente.find('.cantidad-celda');
            let inputCantidad = celdaCantidad.find('input[name="cantidad[]"]');
            let cantidadActual = parseFloat(inputCantidad.val());

            // 2. Sumar la nueva cantidad
            let cantidadTotal = cantidadActual + nuevaCantidad;

            // 3. Actualizamos también el precio unitario en el input oculto correspondiente
            let celdaPrecio = filaExistente.find('.precio-unitario-celda'); // Necesitamos añadir esta clase a la celda de precio
            celdaPrecio.html(`
            ${valorUnitario.toFixed(2)}
            <input type="hidden" name="precio[]" value="${valorUnitario}">
        `);


            // 3. Recalcular el total para esa fila
            let nuevoTotalFila = cantidadTotal * valorUnitario;

            // 4. Actualizar los valores en la tabla (tanto el texto visible como el input oculto)
            celdaCantidad.html(`
            ${cantidadTotal}
            <input type="hidden" name="cantidad[]" value="${cantidadTotal}">
        `);

            filaExistente.find('.total-celda').text(`$ ${nuevoTotalFila.toFixed(2)}`);

            // Mensaje de éxito
            Toast.fire({
                icon: 'success',
                title: 'Producto Actualizado',
                text: `Se actualizó la cantidad de "${productoTexto}".`,
            });

        } else {
            // --- SI EL PRODUCTO NO EXISTE, LO AGREGAMOS (tu código original) ---

            let total = nuevaCantidad * valorUnitario;
            let numeroFila = $('.elementos-agregados').length + 1;

            var nuevaFila = `
            <tr class="elementos-agregados" data-producto-id="${productoId}">
                <td>${numeroFila}</td>
                <td>
                    ${productoTexto}
                    <input type="hidden" name="producto[]" value="${productoId}">
                </td>
                <td class="cantidad-celda">
                    ${nuevaCantidad}
                    <input type="hidden" name="cantidad[]" value="${nuevaCantidad}">
                </td>
                <td class="precio-unitario-celda">
                    $ ${valorUnitario.toFixed(2)}
                    <input type="hidden" name="precio[]" value="${valorUnitario}">
                </td>
                <td class="total-celda total_unitario">
                   $ ${total.toFixed(2)}
                </td>
                <td class="align-middle table-actions">
                    <div class="action-buttons">
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto"><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </td>
            </tr>
        `;

            $('tbody').append(nuevaFila);
            $('#tr-default').hide();
        }

        // --- ESTAS ACCIONES SE EJECUTAN SIEMPRE (tanto al agregar como al actualizar) ---

        // Calcular totales generales de la proforma
        calcularTotales();

        // Limpiar los campos del formulario de entrada
        limpiarCampos(camposAValidar);
        $('#total').val('$ 0.0000'); // Quizás quieras limpiar solo los campos de producto, no los totales
    });

    $('#cantidad, #area_m2, #valor_unitario').on('input', function () {
        let cantidad = tipoProforma == 'adecentamientos' ? $('#cantidad').val() : $('#area_m2').val();
        let valorUnitario = $('#valor_unitario').val() || 0;
        // calcular el total
        let total = cantidad * valorUnitario;
        $('#total').val('$ ' + total.toFixed(2));
    });

    // El listener CORRECTO para reaccionar a los cambios cuando se usa maskMoney (.money)
    $('#valor_unitario').on('keyup blur', function () {
        // Para obtener el valor NUMÉRICO (sin máscara, prefijo, etc.)
        // Usamos el método 'unmasked' del propio plugin.
        // Esto te devolverá un número, por ejemplo: 1234.50
        var valorUnitario = $(this).maskMoney('unmasked')[0];
        let cantidad = tipoProforma == 'adecentamientos' ? $('#cantidad').val() : $('#area_m2').val();
        let total = cantidad * valorUnitario;
        $('#total').val('$ ' + total.toFixed(2));

        // Para obtener el valor FORMATEADO (el que ve el usuario)
        // Simplemente usas .val()
        // Esto te devolverá un string, por ejemplo: "$ 1,234.5000"
        /*var valorFormateado = $(this).val();

        console.log("--- Cambio detectado ---");
        console.log("Valor numérico (para cálculos):", valorUnitario);
        console.log("Valor formateado (para mostrar):", valorFormateado);
        */
        // Aquí ya puedes hacer lo que necesites con el valor,
        // como realizar cálculos, actualizar otros campos, etc.
    });

    $('#porcentaje_iva, #porcentaje_descuento').on('input', function () {
        calcularTotales();
    });

    /**
     * Eliminar elementos de la tabal y recalcular el total
     */
    $(document).on('click', ".eliminar-fila-producto", function () {
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

        calcularTotales();

    });

    $('.guardar').on('click', function (event) {
        event.preventDefault();

        const form = $('#form_proforma');
        const actionType = $(this).attr('id'); // 'nuevo' o 'editar' (o lo que definas)
        const tipoProforma = $('#tipo-proforma').val(); // 'adecentamientos'
        let isValid = true; // Variable para rastrear si el formulario es válido


        const camposAValidar = [
            { selector: '#cliente', mensaje: 'Seleccione el cliente.' },
            { selector: '#fecha', mensaje: 'Seleccione una fecha.' },
        ];

        // Validar campos
        if (!validarCampos(camposAValidar)) {
            isValid = false;
        }

        if ($('.elementos-agregados').length <= 0) {
            isValid = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se ha agregado ningún producto.',
                confirmButtonText: 'Aceptar'
            });
        }

        // Si el formulario no es válido, detenemos todo aquí.
        if (!isValid) {
            return;
        }

        let nuevaUrl;
        const urlBase = `${base_url}/proformas`;

        if (actionType === 'nuevo') {
            nuevaUrl = `${urlBase}/${tipoProforma}/guardar`;
            form.append('<input type="hidden" name="_method" value="POST">');

        } else {
            let urlOriginal = form.attr('action');
            let urlParts = urlOriginal.split('/');
            let proformaId = urlParts[urlParts.length - 1]; // Obtiene el "123"

            // URL: https://sicpro.test/proformas/actualizar/adecentamientos/{id}
            nuevaUrl = `${urlBase}/actualizar/${tipoProforma}/${proformaId}`;
            form.append('<input type="hidden" name="_method" value="PUT">');

        }

        form.attr('action', nuevaUrl);
        // Usamos el método nativo del DOM para evitar conflictos con listeners de jQuery
        form[0].submit();

    });


    $(document).on('click', '.eliminar', function () {
        let id = $(this).attr('id');
        const tipoProforma = $('#tipo-proforma').val(); // 'adecentamientos'

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
                    url: 'eliminar/' + tipoProforma + '/' + id,
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
                                    '<td colspan = "6" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
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

    $('input:text[name=proformas_search]').on('keyup', function () {
        var $value = $(this).val();
        const tipoProforma = $('#tipo-proforma').val();

        $.ajax({
            url: 'buscar/' + tipoProforma,
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

    $("#add-pago-btn").on("click", function () {
        const newPagoHtml = `
            <div class="row mb-2 align-items-center">
                <div class="col-md-7"><input name="forma_pago[]" type="text" class="form-control" placeholder="Nueva descripción de pago"></div>
                <div class="col-md-3"><input name="abono[]" type="text" class="form-control text-end solo-numeros" placeholder="0%"></div>
                <div class="col-md-2 text-end"><button class="btn btn-sm btn-danger remove-pago-btn"><i class="fas fa-trash"></i></button></div>
            </div>`;
        $("#pagos-list").append(newPagoHtml);
    });


    $("#pagos-list").on("click", ".remove-pago-btn", function () {
        $(this).closest(".row").remove();
    });

    // --- Lógica para la sección "INCLUYE" ---
    $("#add-incluye-btn").on("click", function () {
        const newItemHtml = `
            <div class="list-item-input mb-2">
             <div class="row">
                <div class="col-md-10">
                <input type="text" name="incluye[]" class="form-control flex-grow-1" placeholder="Nuevo ítem">
                </div>
                <div class="col-md-2 d-flex  align-items-center">
                    <a href="javascript:void(0);" class="btn btn-sm btn-danger remove-item-btn"><i
                            class="fas fa-trash"></i></a>
                </div>
            </div>
            </div>`;
        $("#incluye-list").append(newItemHtml);
    });

    // Usamos delegación de eventos para los botones de eliminar
    $("#incluye-list").on("click", ".remove-item-btn", function () {
        $(this).closest(".list-item-input").remove();
    });

    // Un contador para asegurar que las nuevas filas tengan IDs únicos en el DOM
    let newRowCounter = 0;
    // 1. AÑADIR FILAS
    // ==================
    $('.add-row-btn').on('click', function () {
        // Encuentra la tabla más cercana a la que pertenece el botón
        const table = $(this).prev('table');
        const tbody = table.find('tbody');
        const categoriaId = table.data('categoria-id');
        newRowCounter++;
        const newRowId = `new_${newRowCounter}`;

        // Plantilla HTML para la nueva fila.
        // Usamos atributos de input para el tipo de dato.
        const newRow = `
            <tr>
                <td><input type="text" name="espacios[${newRowId}][espacio]" class="form-control form-control-sm"></td>
                <td><input type="number" name="espacios[${newRowId}][cantidad]" class="form-control form-control-sm" value="1" min="0"></td>
                <td><textarea name="espacios[${newRowId}][actividades]" class="form-control form-control-sm" rows="1"></textarea></td>
                <td><textarea name="espacios[${newRowId}][mobiliario]" class="form-control form-control-sm" rows="1"></textarea></td>
                <td><input type="number" name="espacios[${newRowId}][usuario]" class="form-control form-control-sm" min="0"></td>
                <td><input type="number" step="0.01" name="espacios[${newRowId}][m2]" class="form-control form-control-sm m2-input" value="0.00" min="0"></td>
                <td><textarea name="espacios[${newRowId}][observaciones]" class="form-control form-control-sm" rows="1"></textarea></td>
                <td><textarea name="espacios[${newRowId}][link_ref]" class="form-control form-control-sm" rows="1" placeholder="Un link por línea"></textarea></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row-btn"><i class="fas fa-trash"></i></button>
                    <input type="hidden" name="espacios[${newRowId}][categoria_id]" value="${categoriaId}">
                </td>
            </tr>
        `;
        tbody.append(newRow);
    });

    // 2. ELIMINAR FILAS
    // ===================
    // Se usa delegación de eventos para que funcione en filas nuevas
    $('body').on('click', '.remove-row-btn', function () {
        $(this).closest('tr').remove();
        // Después de eliminar, recalcular todo
        runAllCalculations();
    });

    // 3. CÁLCULO DE TOTALES
    // =======================
    // Función para calcular el subtotal de M2 para UNA tabla específica
    function calculateSubtotal(table) {
        let subtotal = 0;
        table.find('tbody tr').each(function () {
            // Busca el input de m2 en la fila actual
            const m2Value = parseFloat($(this).find('.m2-input').val()) || 0;
            subtotal += m2Value;
        });
        // Actualiza el texto en el tfoot de la tabla
        table.find('.subtotal-m2').text(subtotal.toFixed(2));
    }

    // Función para calcular los totales generales (Interior / Exterior)
    function calculateGrandTotals() {
        let totalInteriores = 0;
        let totalExteriores = 0;

        $('.table-program').each(function () {
            const table = $(this);
            const esExterior = table.find('.category-header th').text().toUpperCase().includes('EXTERIORES');
            let subtotal = 0;
            table.find('.m2-input').each(function () {
                subtotal += parseFloat($(this).val()) || 0;
            });

            if (esExterior) {
                totalExteriores += subtotal;
            } else {
                totalInteriores += subtotal;
            }
        });

        // Actualiza los totales en la sección final
        // Asegúrate de tener estos elementos con los IDs correspondientes en tu HTML
        $('#total-interiores-val').text(totalInteriores.toFixed(2));
        $('#total-exteriores-val').text(totalExteriores.toFixed(2));
    }

    // Función que llama a todas las calculadoras
    function runAllCalculations() {
        $('.table-program').each(function () {
            calculateSubtotal($(this));
        });
        calculateGrandTotals();
    }

    // Se dispara el cálculo cuando cambia un valor de M2
    $('body').on('input', '.m2-input', function () {
        const table = $(this).closest('table');
        calculateSubtotal(table);
        calculateGrandTotals();
    });

    // Ejecuta todos los cálculos al cargar la página por primera vez
    runAllCalculations();

    function calcularTotales() {
        let descuento = $('#porcentaje_descuento').val() || 0;
        let iva = $('#porcentaje_iva').val() || 0;
        let subtotal = calcularTotal('.elementos-agregados', 2);
        console.log('Subtotal:', subtotal);
        let totalDescuento = descuento > 0 ? subtotal - (subtotal * descuento / 100) : 0;
        let totalIva = (descuento > 0 ? totalDescuento : subtotal) * iva / 100;
        let totalFinal = descuento > 0 ? (parseFloat(totalDescuento) + totalIva) : (parseFloat(subtotal) + totalIva);

        $('#subtotal').text('$ ' + parseFloat(subtotal).toFixed(2));
        $('#totales_descuento').text('$ ' + parseFloat(totalDescuento).toFixed(2));
        $('#totales_iva').text('$ ' + parseFloat(totalIva).toFixed(2));
        $('#total_proforma').text('$ ' + parseFloat(totalFinal).toFixed(2));
    }
});

/*
// --- Lógica para la sección "FORMA DE PAGO" ---
const pagosList = document.getElementById('pagos-list');
const addPagoBtn = document.getElementById('add-pago-btn');

addPagoBtn.addEventListener('click', function () {
    const newPago = document.createElement('div');
    newPago.className = 'row mb-2 align-items-center';
    newPago.innerHTML = `
            <div class="col-md-7"><input type="text" class="form-control" placeholder="Nueva descripción de pago"></div>
            <div class="col-md-3"><input type="text" class="form-control text-end" placeholder="0%"></div>
            <div class="col-md-2 text-end"><button class="btn btn-sm btn-danger remove-pago-btn"><i class="fas fa-trash"></i></button></div>
        `;
    pagosList.appendChild(newPago);
});

pagosList.addEventListener('click', function (e) {
    if (e.target.closest('.remove-pago-btn')) {
        e.target.closest('.row').remove();
    }
});
*/