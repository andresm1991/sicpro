$(function () {
    /**
    * Buscar proveedor
    * @param String
    * return JSON
    */
    $('input:text[name=proveedor_search]').on('keyup', function () {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var $value = $(this).val();

        $.ajax({
            url: 'buscar',
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
   * Eliminar proveedor
   * @param proveedor id
   * return JSON
  */
    $(document).on('click', '.delete-proveedor', function () {
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
                    url: 'eliminar/' + id,
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
                                    '<td colspan = "7" class="text-center text-danger"><strong>No se encontraron datos para mostrar.</strong></td>' +
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

    /*
        $(document).on('click', '#buscar-proveedor', function () {
            var csrf = $('meta[name="csrf-token"]').attr('content');
            var documento = $('input:text[name=documento]').val();
            $.ajax({
                url: 'buscar',
                headers: { 'X-CSRF-TOKEN': csrf },
                type: 'GET',
                data: { 'text': documento },
                beforeSend: function () {
                },
                success: function (data) {
                    if (data) {
                        var form = $('#form_proveedor');
                        var currentAction = form.attr('action');
                        var newEndpoint = 'update/' + data.id;  // Nueva parte final de la URL
                        var newAction = currentAction.replace(/[^/]*$/, newEndpoint);  // Reemplazar solo la parte final
    
                        form.attr('action', newAction);
    
                        form.append('<input name="_method" type="hidden" value="PUT">');
                        telefonoString = data['telefono'];
                        var telefonoArray = telefonoString.split(',').filter(Boolean);
    
                        var inputFields = $('input[name="telefono[]"]');
    
                        inputFields.each(function (index) {
                            if (telefonoArray[index]) {
                                $(this).val(telefonoArray[index]);
                            } else {
                                $(this).val('');  // Limpiar los campos restantes si el array tiene menos de 3 números
                            }
                        });
    
                        $('input:text[name=nombres]').val(data['razon_social']);
                        $('input:text[name=direccion]').val(data['direccion']);
    
                        $('input:text[name=email]').val(data['correo']);
                        $('select[name=banco]').selectpicker('val', data['banco_id']);
                        $('select[name=tipo_cuenta]').selectpicker('val', data['tipo_cuenta_id']);
                        $('input:text[name=numero_cuenta]').val(data['numero_cuenta']);
                        $('select[name=option]').selectpicker('val', data['categoria_id']);
                        $('input:text[name=calificacion]').val(data['calificacion']);
                        $('input:text[name=observacion]').val(data['observacion']);
                    }
    
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                var errors = JSON.parse(jqXHR.responseText);
                console.log(errors)
            });
        });*/
});