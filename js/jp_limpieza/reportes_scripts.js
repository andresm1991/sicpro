import { getFormData } from '../helpers.js';
$(function () {

    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#generar-reporte-adquisicion').click(function () {
        let data = getFormData($('#form-reporte'));
        if (!data.tipo_reporte) {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo de reporte.',
                'error'
            );
            return;
        }

        $.ajax({
            url: 'visualizar-reporte-adquisiciones',
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                if (response.success) {
                    $('#table-view-reporte').html(response.html);

                }
            },
            complete: function () {
                $('#loading').removeClass('show');
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    function formatProveedorAjax(proveedor) {
        // Esta vez el objeto 'proveedor' viene directamente del JSON de nuestra API
        if (proveedor.loading) {
            return proveedor.text;
        }

        // Construimos el HTML con los datos del JSON
        var $container = $(
            "<div class='select2-result-repository clearfix'>" +
            "<div><strong>" + proveedor.text + "</strong></div>" +
            "<div style='font-size: 0.9em; color: #6c757d;'>" +
            "<i class='fa fa-tag'></i> " + proveedor.categoria_descripcion + // Usamos el campo extra
            "</div>" +
            "</div>"
        );

        return $container;
    }

    function formatProveedorSelectionAjax(proveedor) {
        // Esto define cómo se ve el elemento una vez seleccionado
        // Normalmente, solo el texto principal (razon_social) es suficiente
        return proveedor.text || "Busque un proveedor";
    }

    $('#proveedor-select-ajax').select2({
        ajax: {
            url: base_url + "/api/proveedores", // La ruta que creamos
            dataType: 'json',
            delay: 250, // Espera 250ms después de que el usuario deja de escribir
            data: function (params) {
                return {
                    q: params.term // Envía el término de búsqueda como 'q' a la API
                };
            },
            processResults: function (data) {
                // Transforma la respuesta de la API al formato que Select2 espera
                return {
                    results: data.results
                };
            },
            cache: true
        },
        minimumInputLength: 2, // El usuario debe escribir al menos 2 caracteres para buscar
        templateResult: formatProveedorAjax,
        templateSelection: formatProveedorSelectionAjax,
    });

    /**
     * Genera el reporte submit del formulario.
     */
    $('.generar-reporte').on('click', function () {
        const $form = $('#form-reporte');
        let data = getFormData($form);
        const action = $(this).data('action');
        let tipo = $(this).attr('id');

        if (data.tipo_reporte == '') {
            Swal.fire(
                'Ups.!',
                'Por favor seleccione un tipo de reporte.',
                'error'
            )
            return false;
        } else if ((data.tipo_reporte == 'global' || data.tipo_reporte == 'balance') && tipo == 'excel') {
            Swal.fire(
                'Ups.!',
                'Lo sentimos no es posile generar el excel de este reporte. Genera el pdf.',
                'info'
            )
            return false;
        }

        // Agregar el valor de tipo_reporte como parámetro en la URL
        $form.attr('action', `${action}`);



        // Enviar el formulario
        $form.submit();
    });

    $(document).on('click', '.detalle', function () {
        const tipo = $(this).data('tipo');
        const proveedor = $(this).data('proveedor');
        const articulo = $(this).data('articulo');
        const formaData = getFormData($('#form-reporte'));

        let valorEncontrado = $("#tipo-adquisicion option").filter(function () {
            return $(this).text().toLowerCase() === tipo.toLowerCase();
        }).val();

        $.ajax({
            url: 'detalle-reporte-adquisiciones',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: {
                ...formaData,
                tipo: valorEncontrado,
                tipo_detalle: tipo,
                proveedor: proveedor,
                producto: articulo,

            },
            beforeSend: function () {
                $('#loading').addClass('show');
            },
            success: function (response) {
                const data = response.result;

                $('#modal-detalle .modal-title').text(`Detalle de ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}: ${tipo == 'contratista' ? proveedor : articulo}`);
                $('#modal-detalle .modal-body').html(data);
                $('#modal-detalle').modal('show');

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

    $(document).on('click', 'tbody .mano-obra', function () {
        const urlDestino = $(this).data('url');
        window.open(urlDestino, '_blank');
    });
});