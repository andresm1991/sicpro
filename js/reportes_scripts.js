import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    $('.generar-reporte').on('click', function () {
        let tipo_reporte = $('select[name=tipo]').val();
        if (tipo_reporte == '') {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo de reporte.',
                'error'
            )
            return false;
        }
        const action = $(this).data('action'); // Obtener el valor de data-action

        // Actualizar el campo oculto con el tipo de reporte
        $('#tipo_reporte').val(action);

        // Obtener la acción base del formulario (sin modificaciones previas)
        let formAction = $('#form-reporte-adquisiciones').attr('action');

        // Eliminar cualquier sufijo previo agregado al action
        formAction = formAction.split('/pdf')[0].split('/excel')[0];
        // Agregar el valor de tipo_reporte como parámetro en la URL
        $('#form-reporte-adquisiciones').attr('action', `${formAction}/${action}`);

        // Enviar el formulario
        $('#form-reporte-adquisiciones').submit();
    });


    $('#visualizar-reporte').on('click', function () {
        let tipo_reporte_text = $('select[name=tipo] option:selected').text();
        let tipo_reporte = $('select[name=tipo]').val();
        let producto = $('select[name=producto]').val();
        let proveedor = $('select[name=proveedor]').val();
        let cargo = $('select[name=cargo]').val();
        let cargo_text = $('select[name=cargo] option:selected').text();
        let proyecto = $('select[name=proyecto]').val();

        if (tipo_reporte == '') {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo para continuar.',
                'error'
            )
            return false;
        }
        var form = $("#form-reporte-adquisiciones");
        var data = getFormData(form);

        $.ajax({
            url: 'visulizar-reporte-adquisiciones',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                const data = response.result;
                let totalGeneral = 0;

                $('#table-view-reporte').empty();
                console.log(data);
                if (!response.success || data.length == 0) {
                    Swal.fire(
                        'Ups.!',
                        'No se encontraron resultados.',
                        'error'
                    )
                    return;
                }
                if (tipo_reporte_text.toLowerCase() == 'contratistas') {
                    let totalPagado = 0;
                    let totalSaldo = 0;
                    $('#table-view-reporte').append(`<div class="table-responsive">
                        <table class="table table-bordered table-sm" id="table-view-reporte">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">fecha</th>
                                    <th scope="col">proyecto</th>
                                    <th scope="col">porveedor</th>
                                    <th scope="col">producto</th>
                                    <th scope="col">plazo semanas</th>
                                    <th scope="col">etapa</th>
                                    <th scope="col">estado</th>
                                    <th scope="col">total</th>
                                    <th scope="col">abonado</th>
                                    <th scope="col">sando</th>
                                </tr>
                            </thead>
                        <tbody>
                        ${data.map((item) => {

                        totalGeneral += totales(item.total);
                        totalPagado += totales(item.total_pagado);
                        totalSaldo += totales(item.saldo)

                        return `
                                <tr>
                                    <td>${item.contratista.fecha}</td>
                                    <td>${proyecto != 0 ? item.contratista.proyecto.nombre_proyecto : 'GENERAL'}</td>
                                    <td>${item.contratista.proveedor.razon_social}</td>
                                    <td>${item.contratista.articulo.descripcion}</td>
                                    <td>${item.contratista.plazo_semanas}</td>
                                    <td>${item.contratista.etapa.descripcion}</td>
                                    <td>${item.contratista.estado.descripcion}</td>
                                    <td>${item.total}</td>
                                    <td>${item.total_pagado}</td>
                                    <td>${item.saldo}</td>
                                </tr>
                            `;
                    }).join('')}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-right"><strong>Total General:</strong></td>
                                <td colspan="3" class="text-right"><strong> ${formatearUSD(totalGeneral)}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-right"><strong>Total Pagado:</strong></td>
                                <td colspan="3" class="text-right"><strong> ${formatearUSD(totalPagado)}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-right"><strong>Total Saldos:</strong></td>
                                <td colspan="3" class="text-right"><strong> ${formatearUSD(totalSaldo)}</strong></td>
                            </tr>
                        </tfoot>
                        </table>
                        </div>
                        `);
                } else if (tipo_reporte_text.toLowerCase() == 'mano de obra') {
                    $('#table-view-reporte').append(`<div class="table-responsive">
                        <table class="table table-bordered table-sm" id="table-view-reporte">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">proyecto</th>
                                    <th scope="col">semana</th>
                                    <th scope="col">fecha inicio</th>
                                    <th scope="col">fecha fin</th>
                                    <th scope="col">etapa</th>
                                    <th scope="col">actividad</th>
                                    ${cargo != '' ? '<th scope="col">cargo</th>' : ''}
                                    <th scope="col">total</th>
                                </tr>
                            </thead>
                        <tbody>
                        ${data.map((item) => {
                        let valor = item.total;
                        // Paso 1: Eliminar el símbolo "$" y espacios
                        valor = valor.replace('$', '').trim();
                        // Paso 2: Eliminar las comas ","
                        valor = valor.replace(/,/g, '');
                        // Paso 3: Convertir a número
                        valor = parseFloat(valor);

                        totalGeneral += valor

                        return `
                                <tr>
                                    <td>${item.mano_obra.proyecto.nombre_proyecto}</td>
                                    <td>${item.mano_obra.semana}</td>
                                    <td>${item.mano_obra.fecha_inicio}</td>
                                    <td>${item.mano_obra.fecha_fin}</td>
                                    <td>${item.mano_obra.etapa.descripcion}</td>
                                    <td>${item.mano_obra.actividad ? item.mano_obra.actividad.descripcion : ''}</td>
                                    ${cargo != '' ? `<td>${cargo_text}</td>` : ''}
                                    <td>${item.total}</td>
                                </tr>
                            `;
                    }).join('')}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="${cargo != '' ? '6' : '5'}" class="text-right"><strong>Total General:</strong></td>
                                <td colspan="2" class="text-right"><strong> ${formatearUSD(totalGeneral)}</strong></td>
                            </tr>
                        </tfoot>
                        </table>
                        </div>
                        `);
                } else {
                    $('#table-view-reporte').append(`<div class="table-responsive">
                        <table class="table table-bordered table-sm" id="table-view-reporte">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">fecha</th>
                                    <th scope="col">numero</th>
                                    <th scope="col">proveedor</th>
                                    <th scope="col">proyecto</th>
                                    <th scope="col">etapa</th>
                                    <th scope="col">estado</th>
                                    <th scope="col">tipo adquisicion</th>
                                    <th scope="col">factura</th>
                                    ${producto != '' ? '<th scope="col">cantidad</th>' : ''}
                                    <th scope="col">forma pago</th>
                                    <th scope="col">total</th>
                                </tr>
                            </thead>
                        <tbody>
                        ${data.map((item) => {
                        let valor = item.total;
                        // Paso 1: Eliminar el símbolo "$" y espacios
                        valor = valor.replace('$', '').trim();
                        // Paso 2: Eliminar las comas ","
                        valor = valor.replace(/,/g, '');
                        // Paso 3: Convertir a número
                        valor = parseFloat(valor);

                        totalGeneral += valor
                        return `
                                <tr>
                                    <td>${item.adquisicion.fecha}</td>
                                    <td>${item.adquisicion.numero}</td>
                                    <td>${item.adquisicion.orden_recepcion != null ? item.adquisicion.orden_recepcion.proveedor.razon_social : ''}</td>
                                    <td>${item.adquisicion.proyecto.nombre_proyecto}</td>
                                    <td>${item.adquisicion.etapa.descripcion}</td>
                                    <td>${item.adquisicion.estado != 'Completado' ? 'Pendiente' : 'Completado'}</td>
                                    <td>${item.adquisicion.tipo_adquisicion}</td>
                                    <td>${item.adquisicion.factura ?? ''}</td>
                                    ${producto != '' ? `<td>${item.cantidad}</td>` : ''}
                                    <td>${item.adquisicion.orden_recepcion != null ? item.adquisicion.orden_recepcion.forma_pago.descripcion : ''}</td>
                                    <td>${item.total}</td>
                                </tr>
                            `;
                    }).join('')}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="${producto != '' ? '8' : '7'}" class="text-right"><strong>Total General:</strong></td>
                                <td colspan="2" class="text-right"><strong> ${formatearUSD(totalGeneral)}</strong></td>
                            </tr>
                        </tfoot>
                        </table>
                        </div>
                        `);
                }

                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            complete: function () {
                $('#loading').removeClass('show');
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

    $('#visualizar-reporte-gasolina').on('click', function () {
        var form = $("#form-reporte-adquisiciones");
        var data = getFormData(form);

        $.ajax({
            url: 'visulizar-reporte-gasolina',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                const data = response.result;
                let totalGeneral = 0;

                $('#table-view-reporte').empty();
                console.log(data);
                if (!response.success || data.length == 0) {
                    Swal.fire(
                        'Ups.!',
                        'No se encontraron resultados.',
                        'error'
                    )
                    return;
                }


                $('#table-view-reporte').append(`<div class="table-responsive">
                    <table class="table table-bordered table-sm" id="table-view-reporte">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">fecha</th>
                                <th scope="col">dias</th>
                                <th scope="col">kilometraje carga</th>
                                <th scope="col">valor de carga</th>
                                <th scope="col">galones de carga</th>
                                <th scope="col">kilometraje anterior</th>
                                <th scope="col">kilometraje recorrido</th>
                                <th scope="col">kilometraje/galon</th>
                            </tr>
                        </thead>
                    <tbody>
                    ${data.map((item) => {

                    return `
                            <tr>
                                <td>${item.fecha}</td>
                                <td>${item.dias}</td>
                                <td>${item.km_carga}</td>
                                <td>${item.valor}</td>
                                <td>${item.galones}</td>
                                <td>${item.km_anterior}</td>
                                <td>${item.km_recorrido}</td>
                                <td>${item.km_galon}</td>
                            </tr>
                        `;
                }).join('')}
                    </tbody>
                    <tfoot>
                       
                    </tfoot>
                    </table>
                    </div>
                    `);

                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            complete: function () {
                $('#loading').removeClass('show');
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

    $('select[name=tipo]').on('change', function () {
        let tipoText = $('select[name=tipo] option:selected').text();
        $('select[name=proveedor]').empty();
        $('select[name=producto]').empty();
        $('select[name=cargo]').empty();

        if (tipoText != '') {
            tipoText = tipoText.replace(/\s+/g, "_").toLowerCase();
            $.ajax({
                url: 'datos-filtro-reporte-adquisiciones',
                headers: { 'X-CSRF-TOKEN': csrf },
                type: 'GET',
                data: { tipo: tipoText },
                beforeSend: function () {

                },
                success: function (response) {
                    $('select[name=proveedor]').append('<option value=""></option>');
                    $.each(response.result.proveedores, function (index, proveedor) {
                        $('select[name=proveedor]').append(
                            '<option value="' + proveedor.id + '">' + proveedor.razon_social + '</option>'
                        );
                    });

                    $('select[name=producto]').append('<option value=""></option>');
                    $.each(response.result.productos, function (index, value) {
                        $('select[name=producto]').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
                    });

                    $('select[name=cargo]').append('<option value=""></option>');
                    $.each(response.result.cargos, function (index, value) {
                        $('select[name=cargo]').append('<option value="' + value.id + '">' + value.descripcion + '</option>');
                    });

                    habilitarCampos();

                    $('[data-toggle="tooltip"]').tooltip();
                    $('[data-toggle="popover"]').popover({ html: true });
                },
                complete: function () {

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
        }
    });


    function habilitarCampos() {
        let tipo = $('select[name=tipo] option:selected').text();
        tipo = tipo.replace(/\s+/g, "_").toLowerCase();

        if (tipo == 'materiales_y_herramientas' || tipo == 'servicios') {
            $('select[name=proveedor]').attr('disabled', false);
            $('select[name=producto]').attr('disabled', false);
            $('select[name=cargo]').attr('disabled', true);
            $('select[name=necesidad]').attr('disabled', false);
            $('select[name=costo]').attr('disabled', false);
            $('select[name=tipo_reporte]').attr('disabled', false);
            $('select[name=estado]').attr('disabled', false);
            $('select[name=forma_pago]').attr('disabled', false);
        } else if (tipo == 'contratistas') {
            $('select[name=proveedor]').attr('disabled', false);
            $('select[name=producto]').attr('disabled', true);
            $('select[name=cargo]').attr('disabled', true);
            $('select[name=necesidad]').attr('disabled', true);
            $('select[name=costo]').attr('disabled', true);
            $('select[name=tipo_reporte]').attr('disabled', true);
            $('select[name=estado]').attr('disabled', false);
            $('select[name=forma_pago]').attr('disabled', true);
        } else if (tipo == 'mano_de_obra') {
            $('select[name=proveedor]').attr('disabled', true);
            $('select[name=producto]').attr('disabled', true);
            $('select[name=cargo]').attr('disabled', false);
            $('select[name=necesidad]').attr('disabled', true);
            $('select[name=costo]').attr('disabled', true);
            $('select[name=tipo_reporte]').attr('disabled', true);
            $('select[name=estado]').attr('disabled', true);
            $('select[name=forma_pago]').attr('disabled', true);
        }

    }

    function totales($valor) {
        let total = $valor;
        // Paso 1: Eliminar el símbolo "$" y espacios
        total = total.replace('$', '').trim();
        // Paso 2: Eliminar las comas ","
        total = total.replace(/,/g, '');
        // Paso 3: Convertir a número
        total = parseFloat(total);
        return total;
    }
});