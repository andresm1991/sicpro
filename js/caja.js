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

    $(document).on('click', '.eliminarMovimiento', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: '¿Esta Seguro?',
            text: "Una vez se elimina el movimiento no podrá recuperarlo.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, deseo Eliminarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: 'caja/eliminar-movimiento/' + id,
                    headers: { 'X-CSRF-TOKEN': csrf },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message,
                            });

                            $("#" + id).remove();

                            if ($('tbody').children().length == 0) {
                                $('tbody').html('<tr>' +
                                    '<td colspan="10" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
                                    '</tr>');
                            }
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
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
});