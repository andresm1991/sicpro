import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('.generar-reporte').on('click', function () {
        const $form = $('#form-reporte');
        const action = $(this).data('action'); // Obtener el valor de data-action

        let tipo_reporte = $('select[name=tipo]').val();
        let reporte = $('select[name=tipo_reporte]').val();


        if (tipo_reporte == '' && reporte != 'global' && reporte != 'comparativo') {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo de reporte.',
                'error'
            )
            return false;
        } else if (reporte == 'global' && action == 'excel') {
            Swal.fire(
                'Ups.!',
                'Lo sentimos no es posile generar el excel de este reporte. Genera el pdf.',
                'info'
            )
            return false;
        }


        // Actualizar el campo oculto con el tipo de reporte
        $('#tipo_reporte').val(action);

        // Obtener la acción base del formulario (sin modificaciones previas)
        let formAction = $form.attr('action');
        // Eliminar cualquier sufijo previo agregado al action
        formAction = formAction.split('/pdf')[0].split('/excel')[0];
        // Agregar el valor de tipo_reporte como parámetro en la URL
        $form.attr('action', `${formAction}/${action}`);

        // Enviar el formulario
        $form.submit();
    });


    /**
     * Visualizar reportes de adquisiciones
     */
    $('#visualizar-reporte').on('click', function () {
        let tipo_reporte_text = $('select[name=tipo] option:selected').text();
        let tipo_reporte = $('select[name=tipo]').val();
        let reporte = $('select[name=tipo_reporte]').val();
        let producto = $('select[name=producto]').val();
        let proveedor = $('select[name=proveedor]').val();
        let cargo = $('select[name=cargo]').val();
        let cargo_text = $('select[name=cargo] option:selected').text();
        let proyecto = $('select[name=proyecto]').val();

        if (tipo_reporte == '' && reporte != 'global' && reporte != 'comparativo') {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo para continuar.',
                'error'
            )
            return false;
        }
        var form = $("#form-reporte");
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
                let data = response.result;
                let totalGeneral = 0;
                let totalProductos = 0;

                $('#table-view-reporte').empty();
                if (!response.success || data.length == 0) {
                    Swal.fire(
                        'Ups.!',
                        (response && typeof response.mensaje !== 'undefined' && response.mensaje) ? response.mensaje : 'No se encontraron resultados.',
                        'error'
                    )
                    return;
                }
                if (tipo_reporte_text.toLowerCase() == 'contratistas' && reporte != 'global' && reporte != 'comparativo') {
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
                } else if (tipo_reporte_text.toLowerCase() == 'mano de obra' && reporte != 'global' && reporte != 'comparativo') {
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
                } else if (reporte == 'global') { //* Mostrar el reporte global de adquisiciones //
                    let html = '';
                    let totalGeneral = 0;

                    const result = response.result;
                    const proyecto = result.proyecto;
                    const subproyecto = result.subproyecto;
                    const etapasData = result.data; // Este es el objeto con las etapas como claves


                    // Verificar si hay datos de manera correcta (para objetos)
                    if (Object.keys(etapasData).length === 0) {
                        Swal.fire(
                            'Sin resultados',
                            'No se encontraron datos para los filtros seleccionados.',
                            'info'
                        );
                        $('#table-view-reporte').html(''); // Limpiar vista anterior
                        return;
                    }


                    // Construir la cabecera del reporte (fuera del bucle)
                    html += `<h5 class="mt-4 mb-2 text-primary">Proyecto: ${proyecto}</h5>`;
                    if (subproyecto) {
                        html += `<h6 class="mb-2 text-primary">SubProyecto: ${subproyecto}</h6>`;
                    }

                    // 4. Iterar sobre el objeto de etapas usando Object.entries
                    Object.entries(etapasData).forEach(([etapaNombre, categorias]) => {
                        html += '<hr>';
                        html += `<h6 class="mb-2" style="background-color: #e9ecef; padding: 8px; border-radius: 4px;">Etapa: <strong>${etapaNombre}</strong></h6>`;

                        let totalEtapa = 0; // Para sumar el total de esta etapa específica

                        // Tabla de Contratistas
                        if (categorias.contratista && categorias.contratista.length > 0) {
                            let totalContratistas = 0, totalPagos = 0, totalSaldos = 0;
                            html += `<h6 class="mb-1 text-info">Contratistas</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Proveedor</th>
                                                <th>Categoría</th>
                                                <th>Total Contratado</th>
                                                <th>Pagos</th>
                                                <th>Saldo</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                            categorias.contratista.forEach(c => {
                                html += `
                                        <tr>
                                            <td>${c.proveedor}</td>
                                            <td>${c.categoria}</td>
                                            <td class="text-right">${formatearUSD(c.total_contratado)}</td>
                                            <td class="text-right">${formatearUSD(c.pagos)}</td>
                                            <td class="text-right">${formatearUSD(c.saldo)}</td>
                                        </tr>
                                    `;
                                totalContratistas += parseFloat(c.total_contratado);
                                totalPagos += parseFloat(c.pagos);
                                totalSaldos += parseFloat(c.saldo);
                            });
                            html += `</tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="text-right"><strong>Totales Contratista:</strong></td>
                                                <td class="text-right"><strong>${formatearUSD(totalContratistas)}</strong></td>
                                                <td class="text-right"><strong>${formatearUSD(totalPagos)}</strong></td>
                                                <td class="text-right"><strong>${formatearUSD(totalSaldos)}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>`;
                            totalEtapa += totalContratistas;
                        }

                        // Tabla de Mano de Obra
                        if (categorias.mano_obra && categorias.mano_obra.length > 0) {
                            let totalManoObra = 0;
                            html += `<h6 class="mb-1 text-info">Mano de obra</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Semanas/Periodos</th>
                                                <th>Total Pagado</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                            categorias.mano_obra.forEach(m => {
                                html += `
                                        <tr>
                                            <td>${m.cantidad}</td>
                                            <td class="text-right">${formatearUSD(m.total)}</td>
                                        </tr>
                                    `;
                                totalManoObra += parseFloat(m.total);
                            });
                            html += `</tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-right"><strong>Total Mano de Obra:</strong></td>
                                                <td class="text-right"><strong>${formatearUSD(totalManoObra)}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>`;
                            totalEtapa += totalManoObra;
                        }

                        // Tabla de Materiales y Herramientas
                        if (categorias.materiales_herramientas && categorias.materiales_herramientas.length > 0) {
                            let totalMateriales = 0;
                            html += `<h6 class="mb-1 text-info">Materiales y Herramientas</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Unidad</th>
                                                    <th>Cantidad</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;
                            categorias.materiales_herramientas.forEach(mat => {
                                html += `
                                            <tr>
                                                <td>${mat.articulo}</td>
                                                <td>${mat.unidad_medida}</td>
                                                <td>${parseFloat(mat.cantidad_total).toFixed(2)}</td>
                                                <td class="text-right">${formatearUSD(mat.total)}</td>
                                            </tr>
                                        `;
                                totalMateriales += parseFloat(mat.total);
                            });
                            html += `</tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3" class="text-right"><strong>Total Materiales:</strong></td>
                                                        <td class="text-right"><strong>${formatearUSD(totalMateriales)}</strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>`;
                            totalEtapa += totalMateriales;
                        }

                        // Tabla de Servicios
                        if (categorias.servicios && categorias.servicios.length > 0) {
                            let totalServicios = 0;
                            html += `<h6 class="mb-1 text-info">Servicios</h6>
                                        <div class="table-responsive mb-3">
                                            <table class="table table-bordered table-sm">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Unidad</th>
                                                        <th>Cantidad</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                            categorias.servicios.forEach(serv => {
                                html += `
                                                <tr>
                                                    <td>${serv.articulo}</td>
                                                    <td>${serv.unidad_medida}</td>
                                                    <td>${parseFloat(serv.cantidad_total).toFixed(2)}</td>
                                                    <td class="text-right">${formatearUSD(serv.total)}</td>
                                                </tr>
                                            `;
                                totalServicios += parseFloat(serv.total);
                            });
                            html += `</tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3" class="text-right"><strong>Total Servicios:</strong></td>
                                                        <td class="text-right"><strong>${formatearUSD(totalServicios)}</strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>`;
                            totalEtapa += totalServicios;
                        }

                        // Total por Etapa
                        html += `<div class="text-right mb-4">
                                            <h5>Total Etapa ${etapaNombre}: <span class="badge badge-success">${formatearUSD(totalEtapa)}</span></h5>
                                        </div>`;

                        totalGeneral += totalEtapa; // Sumar el total de esta etapa al total general
                    });

                    // Mostrar el Total General del Proyecto al final
                    html += `<hr><div class="text-right mb-4">
                                    <h3>Total General Proyecto: <span class="badge badge-primary" id="total-general-proyecto">${formatearUSD(totalGeneral)}</span></h3>
                                </div>`;
                    html += `<div class="text-right mb-4">
                                <h3>Costos indirectos %: <input type="text" id="costos-indirectos" name="costos_indirectos" class="form-control d-inline-block w-auto solo-numeros" placeholder="0%"></h3>
                            </div>`;
                    html += `<div class="text-right mb-4">
                                <h3>Total General: <span class="badge badge-primary" id="total-general">${formatearUSD(totalGeneral)}</span></h3>
                                <input type="hidden" id="total-general-hidden" name="total_general" value="${totalGeneral}">
                            </div>`;

                    // Usar .html() para reemplazar el contenido, no .append()
                    $('#table-view-reporte').html(html);

                } else if (reporte == 'comparativo') { //* Mostrar el reporte comparativo de adquisiciones //
                    renderizarReporteComparativo(response.result);
                } else { //* Mostrar el reporte de adquisiciones //
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
                                    <th scope="col">necesidades</th>
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
                        totalProductos += item.cantidad;

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
                                    <td>${item.necesidad}</td>
                                </tr>
                            `;
                    }).join('')}
                        </tbody>
                        <tfoot>
                        ${producto != '' ? `<tr>
                                <td colspan="${producto != '' ? '11' : '10'}" class="text-right"><strong>Total Productos:</strong></td>
                                <td class="text-right"><strong>${totalProductos}</strong></td>
                            </tr>` : ''}
                               <tr>
                                <td colspan="${producto != '' ? '11' : '10'}" class="text-right"><strong>Total General:</strong></td>
                                <td class="text-right"><strong> ${formatearUSD(totalGeneral)}</strong></td>
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
        var form = $("#form-reporte");
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
                            '<option value="' + proveedor.id + '">' + proveedor.nombre_proveedor + '</option>'
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

    $('#contenedor-proyecto-2').hide();

    $('select[name=tipo_reporte]').on('change', function () {
        let tipoReporte = $('select[name=tipo_reporte]').val();
        if (tipoReporte == 'comparativo') {
            $('#contenedor-proyecto-2').show();
        } else {
            $('#contenedor-proyecto-2').hide();
        }
    });

    $('select[name=proyecto], select[name=tipo]').on('change', function () {
        let proyectoId = $('select[name=proyecto]').val();
        let tipoId = $('select[name=tipo]').val() || 0;
        let tipoReporte = $('select[name=tipo_reporte]').val();

        if (proyectoId != '') {
            $.ajax({
                url: 'filtro-reporte-adquisiciones/subproyectos/' + proyectoId + '/' + tipoId,
                headers: { 'X-CSRF-TOKEN': csrf },
                type: 'GET',
                beforeSend: function () {
                    $('select[name=subproyecto]').empty();
                },
                success: function (response) {
                    $('select[name=subproyecto]').append('<option value=""></option>');
                    $.each(response.subproyectos, function (index, name) {
                        $('select[name=subproyecto]').append(
                            '<option value="' + index + '">' + name + '</option>'
                        );
                    });

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

    /**
     * Evento para habilitar y deshabilitar el campo de recuperable dependiendo del tipo de solicitud
     * seleccionado en el formulario de REPORTE DE SOLICITUDES
     */

    $('select[name=tipo_solicitud]').on('change', function () {
        let tipoText = $('select[name=tipo_solicitud] option:selected').text();
        if (tipoText != '') {
            tipoText = tipoText.replace(/\s+/g, "_").toLowerCase();
            if (tipoText == 'ausencia') {
                $('select[name=recuperable]').attr('disabled', false);
                $('select[name=recuperable]').val('');
                $('select[name=recuperable]').trigger('change');

                $('select[name=estado]').attr('disabled', false);
                $('select[name=estado]').val('');
                $('select[name=estado]').trigger('change');
            } else if (tipoText == 'reposiciones_global') {
                $('select[name=recuperable]').attr('disabled', true);
                $('select[name=recuperable]').val('');
                $('select[name=recuperable]').trigger('change');

                $('select[name=estado]').attr('disabled', true);
                $('select[name=estado]').val('');
                $('select[name=estado]').trigger('change');
            } else {
                $('select[name=recuperable]').attr('disabled', true);
                $('select[name=recuperable]').val('');
                $('select[name=recuperable]').trigger('change');

                $('select[name=estado]').attr('disabled', false);
                $('select[name=estado]').val('');
                $('select[name=estado]').trigger('change');
            }
        } else {
            $('select[name=recuperable]').attr('disabled', false);
            $('select[name=recuperable]').val('');
            $('select[name=recuperable]').trigger('change');

            $('select[name=estado]').attr('disabled', false);
            $('select[name=estado]').val('');
            $('select[name=estado]').trigger('change');
        }
    });

    /**
     * Evento para visualizar el reporte de SOLICITUDES
     * Se envia el formulario y se recibe la respuesta en formato JSON
     */
    $('#visualizar-reporte-solicitudes').on('click', function () {
        var form = $("#form-reporte");
        var data = getFormData(form);
        let tipoSolicitudText = $('select[name=tipo_solicitud] option:selected').text();

        if (tipoSolicitudText == '') {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione el tipo solicitud para continuar.',
                'error'
            )
            return false;
        }

        tipoSolicitudText = tipoSolicitudText.replace(/\s+/g, "_").toLowerCase();

        $.ajax({
            url: 'visulizar-reporte-solicitudes',
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

                if (!response.success || data.length == 0) {
                    Swal.fire(
                        'Ups.!',
                        'No se encontraron resultados.',
                        'error'
                    )
                    return;
                }

                /// Mostrar el reporte de solicitudes de ausencia
                if (tipoSolicitudText == 'ausencia') {
                    $('#table-view-reporte').append(`<div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Colaborador</th>
                                    <th scope="col">fecha solicitud</th>
                                    <th scope="col">fecha solicitada</th>
                                    <th scope="col">Tiempo total</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Recuperable</th>
                                    <th scope="col">estado</th>
                                    <th scope="col">Detalle</th>
                                </tr>
                            </thead>
                        <tbody>
                        ${data.map((item) => {

                        return `
                                <tr>
                                    <td>${item.usuario.nombre}</td>
                                    <td>${item.fecha_solicitud}</td>
                                    <td>${item.fecha_desde} hasta ${item.fecha_hasta}</td>
                                    <td>${item.tiempo_total}</td>
                                    <td>${item.tipo_solicitud.descripcion}</td>
                                    <td>${item.recuperable > 0 ? 'SI' : 'NO'}</td>
                                    <td>${item.estado_solicitud.descripcion}</td>
                                    <td>${item.detalle}</td>
                                </tr>
                            `;
                    }).join('')}
                        </tbody>
                        <tfoot>
                           
                        </tfoot>
                        </table>
                        </div>
                        `);
                } else if (tipoSolicitudText == 'eventualidad') {
                    $('#table-view-reporte').append(`<div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Colaborador</th>
                                    <th scope="col">fecha</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">estado</th>
                                    <th scope="col">Detalle</th>
                                </tr>
                            </thead>
                        <tbody>
                        ${data.map((item) => {

                        return `
                                <tr>
                                    <td class="align-middle">${item.usuario.nombre}</td>
                                    <td class="align-middle col-sm-1">${item.fecha_solicitud}</td>
                                    <td class="align-middle">${item.tipo_solicitud.descripcion}</td>
                                    <td class="align-middle">${item.estado_solicitud.descripcion}</td>
                                    <td class="align-middle">${item.detalle}</td>
                                </tr>
                            `;
                    }).join('')}
                        </tbody>
                        <tfoot>
                           
                        </tfoot>
                        </table>
                        </div>
                        `);
                } else if (tipoSolicitudText == 'reposiciones_global') {
                    $('#table-view-reporte').append(`<div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Colaborador</th>
                                    <th scope="col">Tiempo solicitado</th>
                                    <th scope="col">Tiempo recuperado</th>
                                </tr>
                            </thead>
                        <tbody>
                        ${data.map((item) => {

                        return `
                                <tr>
                                    <td class="align-middle">${item.usuario.nombre}</td>
                                    <td class="align-middle">${item.tiempo_acumulado_formateado}</td>
                                    <td class="align-middle">${item.tiempo_recuperado_formateado}</td>
                                </tr>
                            `;
                    }).join('')}
                        </tbody>
                        <tfoot>
                           
                        </tfoot>
                        </table>
                        </div>
                        `);
                } else if (tipoSolicitudText == 'reposiciones_detallado') {
                    $('#table-view-reporte').append(`<div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Colaborador</th>
                                    <th scope="col">fecha</th>
                                    <th scope="col">Hora desde</th>
                                    <th scope="col">Hora fin</th>
                                    <th scope="col">tiempo total</th>
                                    <th scope="col">estado</th>
                                    <th scope="col">Motivo</th>
                                </tr>
                            </thead>
                        <tbody>
                        ${data.map((item) => {

                        return item.reposiciones.map((reposicion) => {
                            return `
                                <tr>
                                    <td class="align-middle">${reposicion.usuario.nombre}</td>
                                    <td class="align-middle">${reposicion.fecha}</td>
                                    <td class="align-middle">${reposicion.hora_desde}</td>
                                    <td class="align-middle">${reposicion.hora_hasta}</td>
                                    <td class="align-middle">${reposicion.total}</td>
                                    <td class="align-middle">${reposicion.estado.descripcion}</td>
                                    <td class="align-middle">${reposicion.detalle ?? ''}</td>
                                </tr>
                            `;
                        }).join('');
                    }).join('')}
                        </tbody>
                        <tfoot>
                           
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

    /**
     * Seccion e funciones para habilitar y deshabilitar los campos del formulario
     * dependiendo del tipo de adquisicion seleccionado
     */
    function habilitarCampos() {
        let tipo = $('select[name=tipo] option:selected').text();
        tipo = tipo.replace(/\s+/g, "_").toLowerCase();

        if (tipo == 'materiales_y_herramientas' || tipo == 'servicios') {
            $('select[name=proveedor]').attr('disabled', false);
            $('select[name=producto]').attr('disabled', false);
            $('select[name=cargo]').attr('disabled', true);
            $('select[name=necesidad]').attr('disabled', false);
            $('select[name=costo]').attr('disabled', false);
            //$('select[name=tipo_reporte]').attr('disabled', false);
            $('select[name=estado]').attr('disabled', false);
            $('select[name=forma_pago]').attr('disabled', false);
        } else if (tipo == 'contratistas') {
            $('select[name=proveedor]').attr('disabled', false);
            $('select[name=producto]').attr('disabled', true);
            $('select[name=cargo]').attr('disabled', true);
            $('select[name=necesidad]').attr('disabled', true);
            $('select[name=costo]').attr('disabled', true);
            //$('select[name=tipo_reporte]').attr('disabled', true);
            $('select[name=estado]').attr('disabled', false);
            $('select[name=forma_pago]').attr('disabled', true);
        } else if (tipo == 'mano_de_obra') {
            $('select[name=proveedor]').attr('disabled', true);
            $('select[name=producto]').attr('disabled', true);
            $('select[name=cargo]').attr('disabled', false);
            $('select[name=necesidad]').attr('disabled', true);
            $('select[name=costo]').attr('disabled', true);
            //$('select[name=tipo_reporte]').attr('disabled', true);
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


    $('#generar-reporte-caja').on('click', function () {
        var form = $("#form-reporte");
        var data = getFormData(form);
        var tipoReporte = $('#tipo_reporte').val();
        var ajaxUrl = tipoReporte != 'jp_limpieza' ? 'visulizar-reporte-caja' : 'visulizar-reporte-caja';

        $.ajax({
            url: ajaxUrl,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                const data = response.result.movimientos;
                console.log(data);
                $('#table-view-reporte').empty();

                if (!response.success || data.length == 0) {
                    Swal.fire(
                        'Ups.!',
                        'No se encontraron resultados.',
                        'error'
                    )
                    return;
                }
                $('#table-view-reporte').append(`<div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">fecha</th>
                                <th scope="col">proyecto</th>
                                <th scope="col">subproyecto</th>
                                <th scope="col">descripción</th>
                                <th scope="col">necesidad</th>
                                <th scope="col">proveedor</th>
                                <th scope="col">documento</th>
                                <th scope="col">ingreso</th>
                                <th scope="col">egreso</th>
                                <th scope="col">saldo</th>
                            </tr>
                        </thead>
                    <tbody>
                        <tr>
                            <td class="aling-middle bg-secondary text-white" colspan="9">
                                <strong>${response.result.fecha_saldo_inicio}</strong>
                            </td>
                            <td class="aling-middle bg-secondary text-white">
                                $${response.result.saldo_inicial}
                            </td>
                        </tr>
                        ${data.map((item) => {
                    return `
                                    <tr>
                                        <td>${item.fecha_formateada}</td>
                                        <td class="align-middle">
                                            ${item.origen_id && (item.origen_type == "App\\Models\\Adquisicion" || item.origen_type == "adquisicion_jplimpieza") && item.adquisicion && item.adquisicion.proyecto && item.adquisicion.proyecto.nombre_proyecto ? item.adquisicion.proyecto.nombre_proyecto : '-'}
                                        </td>
                                        <td class="align-middle">
                                            ${(item.origen_id && item.adquisicion && typeof item.adquisicion.subproyecto !== 'undefined' && item.adquisicion.subproyecto !== null && item.adquisicion.subproyecto !== '') ? item.adquisicion.subproyecto : '-'}
                                        </td>
                                        <td>${item.articulo_id != null || item.producto_id != null ? (tipoReporte != 'jp_limpieza' ? item.articulo.descripcion : item.producto.nombre) : item.descripcion}</td>
                                        <td>${item.articulo_id != null || item.producto_id != null ? item.descripcion : '-'}</td>
                                        <td>${item.proveedor != null ? item.proveedor.razon_social : '-'}</td>
                                        <td>${item.referencia ?? '-'}</td>
                                        <td>${item.tipo == 'ingreso' ? item.monto_formatted : '-'}</td>
                                        <td>${item.tipo == 'egreso' ? item.monto_formatted : '-'}</td>
                                        <td>$${item.saldo_acumulado}</td>
                                    </tr>
                                `;
                }).join('')}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="aling-middle">
                                <strong>Sumas totales</strong>
                            </td>
                            <td class="aling-middle">
                                <strong>$${response.result.total_ingresos}</strong>
                            </td>
                            <td class="aling-middle">
                                <strong>$${response.result.total_egresos}</strong>
                            </td>
                            <td class="aling-middle">
                                -
                            </td>
                        </tr>
                        <tr>
                            <td class"aling-middle" colspan="9">
                                <strong>${response.result.fecha_saldo_fin}</strong>
                            </td>
                            <td class"aling-middle">
                                <strong>$${response.result.saldo_final}</strong>
                            </td>
                        </tr>
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

    //* Calcular costos indirectos //
    $(document).on('input', '#costos-indirectos', function () {
        let porcentaje = parseFloat($(this).val()) || 0;
        let totalGeneralProyecto = parsePrecio($('#total-general-proyecto').text());
        let totalGeneral = totalGeneralProyecto / (1 - (porcentaje / 100));
        let valorOficial = phpRound(totalGeneral, 4);
        $('#total-general').text(`${formatearUSD(valorOficial)}`);
        $('#total-general-hidden').val(valorOficial);
    });

    /**
     * Réplica de la función round() de PHP en JavaScript.
     * Redondea un número a una cantidad específica de decimales usando el método "round half up".
     * @param {number} value - El número a redondear.
     * @param {number} decimals - La cantidad de decimales.
     * @returns {number} El número redondeado.
     */
    function phpRound(value, decimals) {
        // Usar notación exponencial para evitar problemas de imprecisión de punto flotante.
        // Esto mueve la coma decimal, redondea el entero y luego la devuelve a su sitio.
        return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
    }

    /**
     * Renderiza un reporte HTML comparativo entre dos proyectos a partir de una respuesta AJAX.
     * @param {object} response El objeto JSON de la respuesta del servidor.
     */
    function renderizarReporteComparativo(response) {

        let html = '';

        // 1. Extraer las variables principales de la respuesta
        const proyecto1 = response.proyecto1;
        const proyecto2 = response.proyecto2;
        const subproyecto = response.subproyecto;
        const dataComparativa = response.data_comparativa;

        // 2. Funciones auxiliares para limpieza y formato
        const formatearMoneda = (valor) => {
            const numero = parseFloat(valor);
            if (isNaN(numero)) return '$0.00';
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(numero);
        };

        const formatearNumero = (valor, decimales = 2) => {
            const numero = parseFloat(valor);
            if (isNaN(numero)) return (0).toFixed(decimales);
            return numero.toFixed(decimales);
        };

        const claseDiferencia = (valor) => {
            const numero = parseFloat(valor);
            if (isNaN(numero) || numero === 0) return 'text-muted';
            return numero > 0 ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
        };

        // 3. Verificar si hay datos para mostrar
        if (!dataComparativa || Object.keys(dataComparativa).length === 0) {
            Swal.fire(
                'Sin resultados',
                'No se encontraron datos para los proyectos y filtros seleccionados.',
                'info'
            );
            $('#table-view-reporte').html('');
            return;
        }

        // 4. Construir la cabecera general del reporte
        html += `<h3 class="mt-4 mb-3 text-primary">Reporte Comparativo de Proyectos</h3>`;
        html += `<div class="row mb-3">
                <div class="col-md-5"><h5>Proyecto 1: <strong>${proyecto1.nombre}</strong></h5></div>
                <div class="col-md-5"><h5>Proyecto 2: <strong>${proyecto2.nombre}</strong></h5></div>
                <div class="col-md-2 text-md-right"><h5>Diferencia (P1 - P2)</h5></div>
             </div>`;
        if (subproyecto) {
            html += `<h6 class="mb-3">Filtro Subproyecto: <strong>${subproyecto}</strong></h6>`;
        }

        // Objeto para acumular los totales generales
        let totalesGenerales = {
            p1: { contratado: 0, mano_obra: 0, materiales: 0, servicios: 0 },
            p2: { contratado: 0, mano_obra: 0, materiales: 0, servicios: 0 }
        };

        // 5. Iterar sobre las etapas
        Object.entries(dataComparativa).forEach(([etapaNombre, categorias]) => {
            html += '<hr>';
            html += `<h5 class="mb-3" style="background-color: #e9ecef; padding: 10px; border-radius: 4px;">Etapa: <strong>${etapaNombre}</strong></h5>`;

            let totalesEtapa = { p1: 0, p2: 0 };

            // --- Tabla de Contratistas ---
            if (categorias.contratista && categorias.contratista.length > 0) {
                let subtotales = { p1: { total: 0, pagos: 0, saldo: 0 }, p2: { total: 0, pagos: 0, saldo: 0 } };
                let tablaHtml = `<h6 class="mb-2 text-info">Contratistas</h6>
                             <div class="table-responsive mb-4">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th rowspan="2" class="align-middle">Proveedor / Categoría</th>
                                            <th colspan="3" class="text-center">${proyecto1.nombre}</th>
                                            <th colspan="3" class="text-center">${proyecto2.nombre}</th>
                                            <th colspan="3" class="text-center">Diferencia</th>
                                        </tr>
                                        <tr>
                                            <th class="text-right">Contratado</th><th class="text-right">Pagos</th><th class="text-right">Saldo</th>
                                            <th class="text-right">Contratado</th><th class="text-right">Pagos</th><th class="text-right">Saldo</th>
                                            <th class="text-right">Contratado</th><th class="text-right">Pagos</th><th class="text-right">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                categorias.contratista.forEach(item => {
                    const p1 = item.p1 || {}; const p2 = item.p2 || {}; const diff = item.diff || {};
                    subtotales.p1.total += (p1.total_contratado || 0); subtotales.p1.pagos += (p1.pagos || 0); subtotales.p1.saldo += (p1.saldo || 0);
                    subtotales.p2.total += (p2.total_contratado || 0); subtotales.p2.pagos += (p2.pagos || 0); subtotales.p2.saldo += (p2.saldo || 0);

                    tablaHtml += `<tr>
                                <td><strong>${item.item_base.proveedor}</strong><br><small class="text-muted">${item.item_base.categoria}</small></td>
                                <td class="text-right">${formatearMoneda(p1.total_contratado)}</td><td class="text-right">${formatearMoneda(p1.pagos)}</td><td class="text-right">${formatearMoneda(p1.saldo)}</td>
                                <td class="text-right">${formatearMoneda(p2.total_contratado)}</td><td class="text-right">${formatearMoneda(p2.pagos)}</td><td class="text-right">${formatearMoneda(p2.saldo)}</td>
                                <td class="text-right ${claseDiferencia(diff.total)}">${formatearMoneda(diff.total)}</td><td class="text-right ${claseDiferencia(diff.pagos)}">${formatearMoneda(diff.pagos)}</td><td class="text-right ${claseDiferencia(diff.saldo)}">${formatearMoneda(diff.saldo)}</td>
                             </tr>`;
                });
                tablaHtml += `</tbody>
                        <tfoot class="table-secondary font-weight-bold">
                            <tr>
                                <td>Totales Contratista</td>
                                <td class="text-right">${formatearMoneda(subtotales.p1.total)}</td><td class="text-right">${formatearMoneda(subtotales.p1.pagos)}</td><td class="text-right">${formatearMoneda(subtotales.p1.saldo)}</td>
                                <td class="text-right">${formatearMoneda(subtotales.p2.total)}</td><td class="text-right">${formatearMoneda(subtotales.p2.pagos)}</td><td class="text-right">${formatearMoneda(subtotales.p2.saldo)}</td>
                                <td class="text-right ${claseDiferencia(subtotales.p1.total - subtotales.p2.total)}">${formatearMoneda(subtotales.p1.total - subtotales.p2.total)}</td>
                                <td class="text-right ${claseDiferencia(subtotales.p1.pagos - subtotales.p2.pagos)}">${formatearMoneda(subtotales.p1.pagos - subtotales.p2.pagos)}</td>
                                <td class="text-right ${claseDiferencia(subtotales.p1.saldo - subtotales.p2.saldo)}">${formatearMoneda(subtotales.p1.saldo - subtotales.p2.saldo)}</td>
                            </tr>
                        </tfoot>
                        </table></div>`;
                html += tablaHtml;
                totalesEtapa.p1 += subtotales.p1.total;
                totalesEtapa.p2 += subtotales.p2.total;
                totalesGenerales.p1.contratado += subtotales.p1.total;
                totalesGenerales.p2.contratado += subtotales.p2.total;
            }

            // --- Función genérica para Materiales y Servicios ---
            const renderizarTablaItems = (titulo, items, tipo) => {
                if (!items || items.length === 0) return '';
                let subtotales = { p1: { cantidad: 0, total: 0 }, p2: { cantidad: 0, total: 0 } };
                let tablaHtml = `<h6 class="mb-2 text-info">${titulo}</h6>
                             <div class="table-responsive mb-4">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th rowspan="2" class="align-middle">Item / Unidad</th>
                                            <th colspan="2" class="text-center">${proyecto1.nombre}</th>
                                            <th colspan="2" class="text-center">${proyecto2.nombre}</th>
                                            <th colspan="2" class="text-center">Diferencia</th>
                                        </tr>
                                        <tr>
                                            <th class="text-right">Cantidad</th><th class="text-right">Total</th>
                                            <th class="text-right">Cantidad</th><th class="text-right">Total</th>
                                            <th class="text-right">Cantidad</th><th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                items.forEach(item => {
                    const p1 = item.p1 || {}; const p2 = item.p2 || {}; const diff = item.diff || {};
                    subtotales.p1.cantidad += (p1.cantidad_total || 0); subtotales.p1.total += (p1.total || 0);
                    subtotales.p2.cantidad += (p2.cantidad_total || 0); subtotales.p2.total += (p2.total || 0);

                    tablaHtml += `<tr>
                                <td><strong>${item.item_base.articulo}</strong><br><small class="text-muted">${item.item_base.unidad_medida}</small></td>
                                <td class="text-right">${formatearNumero(p1.cantidad_total)}</td><td class="text-right">${formatearMoneda(p1.total)}</td>
                                <td class="text-right">${formatearNumero(p2.cantidad_total)}</td><td class="text-right">${formatearMoneda(p2.total)}</td>
                                <td class="text-right ${claseDiferencia(diff.cantidad)}">${formatearNumero(diff.cantidad)}</td><td class="text-right ${claseDiferencia(diff.total)}">${formatearMoneda(diff.total)}</td>
                             </tr>`;
                });
                tablaHtml += `</tbody>
                          <tfoot class="table-secondary font-weight-bold">
                              <tr>
                                  <td>Totales ${titulo}</td>
                                  <td class="text-right">${formatearNumero(subtotales.p1.cantidad)}</td><td class="text-right">${formatearMoneda(subtotales.p1.total)}</td>
                                  <td class="text-right">${formatearNumero(subtotales.p2.cantidad)}</td><td class="text-right">${formatearMoneda(subtotales.p2.total)}</td>
                                  <td class="text-right ${claseDiferencia(subtotales.p1.cantidad - subtotales.p2.cantidad)}">${formatearNumero(subtotales.p1.cantidad - subtotales.p2.cantidad)}</td>
                                  <td class="text-right ${claseDiferencia(subtotales.p1.total - subtotales.p2.total)}">${formatearMoneda(subtotales.p1.total - subtotales.p2.total)}</td>
                              </tr>
                          </tfoot>
                          </table></div>`;
                totalesEtapa.p1 += subtotales.p1.total;
                totalesEtapa.p2 += subtotales.p2.total;
                totalesGenerales.p1[tipo] += subtotales.p1.total;
                totalesGenerales.p2[tipo] += subtotales.p2.total;
                return tablaHtml;
            };

            html += renderizarTablaItems('Materiales y Herramientas', categorias.materiales_herramientas, 'materiales');
            html += renderizarTablaItems('Servicios', categorias.servicios, 'servicios');

            // --- Tabla de Mano de Obra ---
            if (categorias.mano_obra && categorias.mano_obra.length > 0) {
                let item = categorias.mano_obra[0]; // Mano de obra está consolidada
                const p1 = item.p1 || {}; const p2 = item.p2 || {}; const diff = item.diff || {};

                html += `<h6 class="mb-2 text-info">Mano de Obra</h6>
                     <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Concepto</th>
                                    <th class="text-center">${proyecto1.nombre}</th>
                                    <th class="text-center">${proyecto2.nombre}</th>
                                    <th class="text-center">Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Pagado Mano de Obra</td>
                                    <td class="text-right">${formatearMoneda(p1.total)}</td>
                                    <td class="text-right">${formatearMoneda(p2.total)}</td>
                                    <td class="text-right ${claseDiferencia(diff.total)}">${formatearMoneda(diff.total)}</td>
                                </tr>
                            </tbody>
                        </table>
                     </div>`;

                totalesEtapa.p1 += (p1.total || 0);
                totalesEtapa.p2 += (p2.total || 0);
                totalesGenerales.p1.mano_obra += (p1.total || 0);
                totalesGenerales.p2.mano_obra += (p2.total || 0);
            }

            // --- Total por Etapa ---
            html += `<div class="text-right mb-4">
                    <h5 class="d-inline-block p-2 rounded" style="background-color: #f8f9fa;">
                        Total Etapa: 
                        <span class="badge badge-light mx-2">${formatearMoneda(totalesEtapa.p1)}</span> vs 
                        <span class="badge badge-light mx-2">${formatearMoneda(totalesEtapa.p2)}</span>
                        <span class="badge badge-info ml-2">Diff: ${formatearMoneda(totalesEtapa.p1 - totalesEtapa.p2)}</span>
                    </h5>
                 </div>`;
        });

        // 6. Resumen Final Comparativo
        const totalP1 = Object.values(totalesGenerales.p1).reduce((a, b) => a + b, 0);
        const totalP2 = Object.values(totalesGenerales.p2).reduce((a, b) => a + b, 0);

        html += `<hr class="mt-5"><div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Resumen General de Costos</h4>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Concepto</th>
                                <th class="text-right">${proyecto1.nombre}</th>
                                <th class="text-right">${proyecto2.nombre}</th>
                                <th class="text-right">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Contratistas</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p1.contratado)}</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p2.contratado)}</td>
                                <td class="text-right ${claseDiferencia(totalesGenerales.p1.contratado - totalesGenerales.p2.contratado)}">${formatearMoneda(totalesGenerales.p1.contratado - totalesGenerales.p2.contratado)}</td>
                            </tr>
                             <tr>
                                <td>Materiales y Herramientas</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p1.materiales)}</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p2.materiales)}</td>
                                <td class="text-right ${claseDiferencia(totalesGenerales.p1.materiales - totalesGenerales.p2.materiales)}">${formatearMoneda(totalesGenerales.p1.materiales - totalesGenerales.p2.materiales)}</td>
                            </tr>
                            <tr>
                                <td>Servicios</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p1.servicios)}</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p2.servicios)}</td>
                                <td class="text-right ${claseDiferencia(totalesGenerales.p1.servicios - totalesGenerales.p2.servicios)}">${formatearMoneda(totalesGenerales.p1.servicios - totalesGenerales.p2.servicios)}</td>
                            </tr>
                             <tr>
                                <td>Mano de Obra</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p1.mano_obra)}</td>
                                <td class="text-right">${formatearMoneda(totalesGenerales.p2.mano_obra)}</td>
                                <td class="text-right ${claseDiferencia(totalesGenerales.p1.mano_obra - totalesGenerales.p2.mano_obra)}">${formatearMoneda(totalesGenerales.p1.mano_obra - totalesGenerales.p2.mano_obra)}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr class="font-weight-bold">
                                <td><h5>Total General</h5></td>
                                <td class="text-right"><h5>${formatearMoneda(totalP1)}</h5></td>
                                <td class="text-right"><h5>${formatearMoneda(totalP2)}</h5></td>
                                <td class="text-right ${claseDiferencia(totalP1 - totalP2)}"><h5>${formatearMoneda(totalP1 - totalP2)}</h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>`;

        // 7. Inyectar el HTML final en el contenedor de la vista
        $('#table-view-reporte').html(html);
    }
});