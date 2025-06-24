import { getFormData, limpiarFormulario } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var success = false;
    var tipoCaja = "";

    $('#modalMovimientoCaja').on('shown.bs.modal', function (e) {
        var button = $(e.relatedTarget);
        // Obtiene el valor de data-caja
        tipoCaja = button.data('caja');

        $('.select2-basic-single').select2({
            width: '100%',
            dropdownParent: $('#modalMovimientoCaja'),
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
    });

    $('#modalMovimientoCaja').on('hidden.bs.modal', function (e) {
        if (success) {
            location.reload();
        }
    });

    $("#registrar_movimiento").on('click', function (e) {
        e.preventDefault();

        var form = $("#form_movimiento_caja");
        var data = getFormData(form);

        const camposAValidar = [
            { selector: '#monto', mensaje: 'Ingrese un valor.' },
            { selector: '#tipo_movimiento', mensaje: 'Seleccione la opción.' },
            { selector: '#detalle', mensaje: 'Ingrese el detalle del movimiento.' },
        ];

        // Validar campos
        if (!validarCampos(camposAValidar)) {
            return; // Detener si hay errores
        }

        $.ajax({
            url: 'caja/guardar',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#modal-overlay').show();
            },
            success: function (response) {
                console.log(response)
                success = response.success;
                $('#message').html(`
                    <div class="alert ${response.success ? "alert-success" : "alert-danger"} alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas ${response.success ? "fa-check" : "fa-ban"}"></i> ${response.message}</h5>
                    </div>
                `);

                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });

            }, error: function (xhr, status, error) {  // Función si hay un error (404, 500, etc.)
                success = false;
                console.error("Error en la petición:", error);
                var errors = JSON.parse(xhr.responseText);
                console.log(errors);
                $('#message').html('<div class="alert alert-danger alert-dismissible">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                    '<h5><i class="icon fas fa-ban"></i> Ocurrio un error, por favor intente nuevamente.</h5>' +
                    '</div>');
            },
            complete: function () {         // Función que se ejecuta al finalizar (éxito o error)
                $('#modal-overlay').hide();
            }
        });
    });
});