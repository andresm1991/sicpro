import { getFormData } from './helpers.js';

$(function () {
    var articulo_id = 0;

    $(document).on('click', '.editar_articulo', function () {
        articulo_id = $(this).data('id');
        var estado = $(this).data('estado');

        $('select[name=categoria]').selectpicker('val', $(this).data('categoria'));
        $('input:text[name=codigo]').val($(this).data('codigo'));
        $('input:text[name=descripcion]').val($(this).data('descripcion'));
        $('input:radio[name=status]').filter('[value="' + estado + '"]').prop('checked', true);

        $('#titleArticuloFormModal').text('Editar Producto');
        $('#articuloFormModal').modal('show');
    });

    $('#articuloFormModal').on('hidden.bs.modal', function (e) {
        $('#message').hide();
        $('.input_errors').remove();
        $("#from-articulo")[0].reset();
        $('#titleArticuloFormModal').text('Nuevo Producto');
        articulo_id = 0;
    });
    /**
     * Guardar datos del fromulario articulos
     * @param form
     * return JSON
     */
    $('#guardar-articulo').click(function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var form = $("#from-articulo");
        var data = getFormData(form);

        var url = '', type = '';
        if (articulo_id > 0) {
            url = 'productos/actualizar/' + articulo_id;
            type = 'PUT';
        } else {
            url = 'productos/nuevo';
            type = 'POST';
        }

        $.ajax({
            url: url,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: type,
            data: data,
            beforeSend: function () {
                $('#message').html('');
                $('.input_errors').remove();
            },
            success: function (data) {
                $('#message').show();

                if (data.success) {
                    $('tbody').html(data.articulos);

                    $('#message').html('<div class="alert alert-success alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-check"></i> ' + data.mensaje + '</h5>' +
                        '</div>');
                } else {
                    $('#message').html('<div class="alert alert-danger alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-ban"></i> ' + data.mensaje + '</h5>' +
                        '</div>');
                }
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            switch (jqXHR.status) {
                case 422: // ERROR INPUT VALIDATE
                    $.each(jqXHR.responseJSON.errors, function (i, error) {
                        if (i == 'categoria') {
                            $('.error_categoria').html('<span class="input_errors" style="color: red;">' + error[0] + '</span>');
                        } else {
                            var el = $(document).find('[name="' + i + '"]');
                            el.after($('<span class="input_errors" style="color: red;">' + error[0] + '</span>'));
                        }

                    });
                    break;

                case 419: // ERROR EXPIRATE SESSION
                    window.location = '/';
                    break;

                default:
                    var errors = JSON.parse(jqXHR.responseText);
                    console.log(errors)
            }
        });

    });

    /**
     * Buscar Articulos
     * @param String
     * return JSON
    */
    $('input:text[name=articulo_search]').on('keyup', function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var $value = $(this).val();

        $.ajax({
            url: 'productos/buscar',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'text': $value },
            beforeSend: function () {
            },
            success: function (data) {
                $('tbody').html(data);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    /**
    * Eliminar articulo
    * @param articulo id
    * return JSON
   */
    $(document).on('click', '.delete-articulo', function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');
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
                    url: 'productos/eliminar/' + id,
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
                                    '<td colspan = "5" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
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
        })
    });
});