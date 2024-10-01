$(function(){
    var csrf = $('meta[name="csrf-token"]').attr('content');
    /**
     * Cargar la categoria de cada proveedor cuando se selecciona
     */
    $('#proveedor').on('change', function (e) {
        // Remueve la clase 'error-border' del contenedor generado por select2
        $(this).closest('.form-group').find('.select2-selection').removeClass('error-border');

        // Elimina solo el mensaje de error asociado con este select2
        $(this).closest('.form-group').find('.error-message').remove();

        // Obtén el valor seleccionado
        var selected_value = $(this).val();
        $.ajax({
            url: '/articulos-proveedor',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'proveedor': selected_value },
            beforeSend: function () {
            },
            success: function (response) {
                if (response.success) {
                    // Elimina las opciones anteriores del select
                    let $select = $('#categoria');
                    $select.empty(); // Vacia el select

                    // Itera sobre los artículos y crea nuevas opciones
                    $.each(response.articulos, function (index, articulo) {
                        let option = new Option(articulo.nombre, articulo.id, false, false);
                        $select.append(option); // Añade la opción al select
                    });

                    // Inicializa o actualiza Select2
                    $select.select2({
                        width: '100%'
                    });

                    // Remueve la clase 'error-border' del contenedor generado por select2
                    $($select).closest('.form-group').find('.select2-selection').removeClass('error-border');

                    // Elimina solo el mensaje de error asociado con este select2
                    $($select).closest('.form-group').find('.error-message').remove();
                }

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });
});