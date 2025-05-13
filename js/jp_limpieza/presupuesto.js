import { getFormData, limpiarFormulario } from '../helpers.js';

$(function () {

    var csrf = $('meta[name="csrf-token"]').attr('content');

    // Función para formatear el precio (ejemplo: "1,500.50" → 1500.50)
    function parsePrecio(texto) {
        return parseFloat(texto.replace(/[^\d.-]/g, '')) || 0;
    }

    $('.select2-basic-single').select2({
        width: '100%',
        dropdownParent: $('#modalRubrosPresupuesto'),
        placeholder: function () {
            $(this).data('placeholder');
        },
        allowClear: false,
    });

    $('#modalRubrosPresupuesto').on('shown.bs.modal', function (e) {
        $('.select2-tag').select2({
            width: '100%',
            dropdownParent: $('#modalRubrosPresupuesto'),
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

    $('#modalRubrosPresupuesto').on('hidden.bs.modal', function (e) {
        location.reload();
    });

    //** Cargar la categoria del rubro selecionado */
    $('#categoria').on('change', function (e) {
        // Remueve la clase 'error-border' del contenedor generado por select2
        $(this).closest('.form-group').find('.select2-selection').removeClass('error-border');
        // Elimina solo el mensaje de error asociado con este select2
        $(this).closest('.form-group').find('.error-message').remove();

        let $select = $('#rubros');
        $select.empty(); // Vacia el select rubros

        // Obtén el valor seleccionado
        var selected_value = $(this).val();
        if (isNaN(selected_value)) {
            $('input[name=valor]').val(0);
            return;
        }
        // 1. Obtener el option seleccionado
        const selectedOption = $(this).find('option:selected');
        // 2. Acceder a data-detalle
        const detalle = selectedOption.data('detalle'); // o .attr('data-detalle')
        $('#detalle').val(detalle);

        $.ajax({
            url: presupuestoUrl + '/rubros-presupuesto',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'categoria': selected_value },
            beforeSend: function () {
            },
            success: function (response) {
                console.log(response)
                // Itera sobre los artículos y crea nuevas opciones
                $.each(response.rubros, function (index, rubro) {
                    let option = new Option(rubro.descripcion, rubro.id, false, false);
                    $select.append(option); // Añade la opción al select

                });

                $select.trigger('change');
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    //** Cargar el precio unitario del rubro selecionado */
    $('#rubros').on('change', function () {
        if ($('input[name=rubro_presupuesto_id]').val() != '') {
            let selectedOption = $(this).find('option:selected');
            let precio = selectedOption.data('valor_unitario');
            $('#precio_unitario').val(precio);
        }
    });

    /// calcular el total
    $('#cantidad, #precio_unitario, #meses').on('keyup', function () {
        let cantidad = parseFloat($('#cantidad').val()) || 0;
        let precio_unitario_texto = $('#precio_unitario').val().trim(); // Elimina espacios al inicio y final
        let precio_unitario = parseFloat(precio_unitario_texto.replace(/[^\d.-]/g, '')) || 0; // Elimina todo excepto números, puntos y signo negativo
        let meses = parseFloat($('#meses').val()) || 0;
        let total = cantidad * precio_unitario * meses;

        // Mostrar el resultado (ejemplo en un input con id "total")
        $('#total').text(total.toFixed(4)); // Formatea a 2 decimales
    });

    // Evento para inputs de cantidad, precio_unitario y meses
    $('body').on('keyup', '.cantidad, .precio_unitario, .meses', function () {
        let id = $(this).data('id'); // ID del hijo (ejemplo: 2 para "Auxiliares")
        let cantidad = parseFloat($('.cantidad[data-id="' + id + '"]').val()) || 0;
        let precioTexto = $('.precio_unitario[data-id="' + id + '"]').val();
        let precio = parsePrecio(precioTexto);
        let meses = parseFloat($('.meses[data-id="' + id + '"]').val()) || 0;
        let subtotal = cantidad * precio;
        let total = cantidad * precio * meses;

        // Actualizar el campo "subtotal" de la fila correspondiente
        $('.subtotal[data-id="' + id + '"]').text('$' + subtotal.toFixed(4));
        // Actualizar el campo "total" de la fila correspondiente
        $('.total[data-id="' + id + '"]').text('$' + total.toFixed(4));
    });

    /// Agregar rubro
    $('#agergar-rubro').on('click', function () {
        var form = $("#form_rubros_presupuesto");
        var data = getFormData(form);

        const camposAValidar = [
            { selector: '#categoria', mensaje: 'Seleccione el categoria.' },
            { selector: '#rubros', mensaje: 'Seleccione la opción.' },
            { selector: '#cantidad', mensaje: 'Ingrese cantidad.' },
            { selector: '#precio_unitario', mensaje: 'Ingrese valor.' },
            { selector: '#meses', mensaje: 'Ingrese valor.' },
        ];

        // Validar campos
        if (!validarCampos(camposAValidar)) {
            return; // Detener si hay errores
        }

        $.ajax({
            headers: { 'X-CSRF-TOKEN': csrf },
            url: presupuestoUrl + '/guardar-rubro',
            type: 'POST',
            data: data,
            beforeSend: function () {       // Función que se ejecuta antes de enviar (opcional)
                $('#modal-overlay').show();
            },
            success: function (response) {  // Función si la petición es exitosa (200 OK)
                $('#message').html('<div class="alert' + (response.success ? ' alert-success' : ' alert-danger') + ' alert-dismissible">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                    '<h5><i class="icon fas fa-' + (response.success ? 'check' : 'ban') + '"></i> ' + response.mensaje + '</h5>' +
                    '</div>');

                limpiarFormulario(form);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            error: function (xhr, status, error) {  // Función si hay un error (404, 500, etc.)
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

    /// Editar rubto
    $(document).on('click', '.editar-rubro', function () {
        var id = $(this).attr('id');
        var categoria = $(this).data('categoria_id');
        var cantidad = $(this).data('cantidad');
        var precio_unitario = $(this).data('valor_unitario');
        var meses = $(this).data('meses');

        $('#titleModal').text('Editar Rubro');
        $('#guardar').text('Actualizar');
        $('input:hidden[name=rubro_presupuesto_id]').val(id);
        $('#categoria').val(categoria).trigger('change');
        $('#rubros').val(id).trigger('change');
        $('#cantidad').val(cantidad);
        $('#precio_unitario').val(precio_unitario);
        $('#meses').val(meses);


    });

});