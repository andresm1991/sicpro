$(function () {
    /**
     * Agregar producto a la proforma
     */
    $('#agregar-producto').on('click', function () {
        let producto = $('#producto').val();
        let cantidad = $('#cantidad').val();
        let valorUnitario = $('#valor_unitario').val();
        let iva = $('#iva').val();
        let unidadMedida = $('#unidad_medida').val();
        let tipoProforma = $('#tipo_proforma').val();
        var valid = true;

        const camposAValidar = [
            { selector: '#producto', mensaje: 'Seleccione el producto.' },
            { selector: '#unidad_medida', mensaje: 'Seleccione la opción.' },
            { selector: '#cantidad', mensaje: 'Ingrese cantidad.' },
            { selector: '#valor_unitario', mensaje: 'Ingrese valor.' },
            { selector: '#iva', mensaje: 'Ingrese el IVA.' },
        ];

        // Validar campos
        if (!validarCampos(camposAValidar)) {
            valid = false;
        }

        if (!valid) {
            return;
        }

        // calcular el total
        let total = cantidad * valorUnitario;
        let totalIva = (total * iva) / 100;
        let totalFinal = total + totalIva;

        numeroFila = $('.elementos-agregados').length + 1;

        if (tipoProforma == 'diseno_planos') {
            unidadMedida = `<td>
                ${$('#unidad_medida option:selected').text()}
                <input type="hidden" name="unidad_medida[]" value="${unidadMedida}">
            </td>`;
        }

        // Crear una nueva fila con los datos
        var nuevaFila = `
            <tr class="elementos-agregados">
                <td>${numeroFila}</td>
                <td>
                    ${$('#producto option:selected').text()}
                    <input type="hidden" name="producto[]" value="${producto}">
                </td>
                <td>
                    ${cantidad}
                    <input type="hidden" name="cantidad[]" value="${cantidad}">
                </td>
                ${unidadMedida}
                <td>
                    $ ${valorUnitario}
                    <input type="hidden" name="precio[]" value="${valorUnitario}">
                </td>
                <td>
                    ${iva}
                    <input type="hidden" name="iva[]" value="${iva}">
                </td>
                <td class="total_unitario">
                   $ ${totalFinal.toFixed(4)}
                </td>
                <td class="align-middle table-actions">
                    <div class="action-buttons">
                        <a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-fila-producto" id=""><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </td>
            </tr>
        `;

        // Agregar la nueva fila a la tabla
        $('tbody').append(nuevaFila);

        // Ocultar tr por defecto
        $('#tr-default').hide();
        // Calcular totales
        calcularTotales();
        // Limpiar campos 
        limpiarCampos(camposAValidar);
        $('#total').val('$ 0.0000');
    });

    $('#cantidad, #valor_unitario, #iva').on('input', function () {
        let cantidad = $('#cantidad').val();
        let valorUnitario = $('#valor_unitario').val();
        let iva = $('#iva').val();

        // calcular el total
        let total = cantidad * valorUnitario;
        let totalIva = (total * iva) / 100;
        let totalFinal = total + totalIva;

        $('#total').val('$ ' + totalFinal.toFixed(2));
    });

    $('#porcentaje_iva, #porcentaje_descuento').on('input', function () {
        calcularTotales();

    });

    function calcularTotales() {
        let descuento = $('#porcentaje_descuento').val() || 0;
        let iva = $('#porcentaje_iva').val() || 0;
        let subtotal = calcularTotal('.elementos-agregados', 2);
        let totalDescuento = descuento > 0 ? subtotal - (subtotal * descuento / 100) : 0;
        let totalIva = (descuento > 0 ? totalDescuento : subtotal) * iva / 100;
        let totalFinal = descuento > 0 ? (parseFloat(totalDescuento) + totalIva) : (parseFloat(subtotal) + totalIva);

        $('#subtotal').text('$ ' + parseFloat(subtotal).toFixed(2));
        $('#totales_descuento').text('$ ' + parseFloat(totalDescuento).toFixed(2));
        $('#totales_iva').text('$ ' + parseFloat(totalIva).toFixed(2));
        $('#total_proforma').text('$ ' + parseFloat(totalFinal).toFixed(2));
    }
});