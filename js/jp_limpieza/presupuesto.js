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


    //** Filtrar rubros por nombre o categorias */
    $('input:text[name=rubros_search]').on('keyup', function () {
        let filtro = $(this).val().toLowerCase().trim();
        let mostrarTodo = filtro === '';

        // Iterar sobre todas las filas de la tabla
        $('#table-rubros-presupuesto tbody tr').each(function () {
            let $fila = $(this);
            let esCategoriaPadre = $fila.hasClass('fila-categoria');
            let esRubroHijo = $fila.hasClass('fila-rubro');
            let contenidoFila = $fila.find('.filtrable').text().toLowerCase();
            let categoriaId = $fila.data('categoria-id');
            let padreId = $fila.data('padre-id');

            if (mostrarTodo) {
                $fila.show();
                return;
            }

            // Si es una categoría padre
            if (esCategoriaPadre) {
                if (contenidoFila.includes(filtro)) {
                    $fila.show();
                    // Mostrar todos los rubros hijos de esta categoría
                    $(`tr[data-padre-id="${categoriaId}"].fila-rubro`).show();
                } else {
                    // Verificar si algún hijo coincide con el filtro
                    let hijosCoinciden = false;
                    $(`tr[data-padre-id="${categoriaId}"].fila-rubro`).each(function () {
                        if ($(this).find('.filtrable').text().toLowerCase().includes(filtro)) {
                            hijosCoinciden = true;
                            return false; // Salir del each
                        }
                    });

                    if (hijosCoinciden) {
                        $fila.show(); // Mostrar categoría aunque no coincida
                    } else {
                        $fila.hide(); // Ocultar categoría y sus hijos
                        $(`tr[data-padre-id="${categoriaId}"].fila-rubro`).hide();
                    }
                }
            }

            // Si es un rubro hijo
            if (esRubroHijo) {
                if (contenidoFila.includes(filtro)) {
                    $fila.show();
                    // Mostrar la categoría padre
                    $(`tr[data-id="${padreId}"].fila-categoria`).show();
                } else {
                    // Ocultar solo si la categoría padre no coincide
                    let padreCoincide = $(`tr[data-id="${padreId}"].fila-categoria .filtrable`)
                        .text().toLowerCase().includes(filtro);

                    if (!padreCoincide) {
                        $fila.hide();
                    }
                }
            }
        });

        // Manejar filas de totales y separadores
        $('#table-rubros-presupuesto tbody tr').each(function () {
            let $fila = $(this);
            if ($fila.hasClass('fila-total') || $fila.hasClass('separador')) {
                let categoriaId = $fila.prevAll('.fila-categoria:first').data('id');
                let categoriaVisible = $(`tr[data-id="${categoriaId}"].fila-categoria`).is(':visible');
                let algunHijoVisible = $(`tr[data-padre-id="${categoriaId}"].fila-rubro`).is(':visible');

                if (categoriaVisible || algunHijoVisible) {
                    $fila.show();
                } else {
                    $fila.hide();
                }
            }
        });
    });


    $(document).on('click', '.editar-categoria', function () {
        let fila = $(this).closest('tr');
        fila.find('.texto-categoria-descripcion').hide();
        fila.find('.texto-categoria-detalle').hide();
        fila.find('.edicion-categoria').show();
    });

    // Cancelar edición categoría
    $(document).on('click', '.btn-cancelar-categoria', function (e) {
        e.preventDefault();
        e.stopPropagation();

        // 1. Encontrar la fila padre
        const $fila = $(this).closest('tr.fila-categoria');
        if ($fila.length === 0) {
            console.error('No se encontró la fila de categoría');
            return;
        }

        // 2. Encontrar los elementos específicos
        const $contenedorEdicion = $fila.find('.edicion-categoria');
        const $textoOriginalDescripcion = $fila.find('.texto-categoria-descripcion');
        const $textoOriginalDetalle = $fila.find('.texto-categoria-detalle');

        // 3. Verificar que existen
        if ($contenedorEdicion.length === 0 || $textoOriginalDescripcion.length === 0 || $textoOriginalDetalle.length === 0) {
            console.error('No se encontraron los elementos de edición/texto');
            return;
        }

        // 4. Mostrar/ocultar con animación para mejor feedback
        $contenedorEdicion.fadeOut(300, function () {
            $textoOriginalDescripcion.fadeIn(300);
            $textoOriginalDetalle.fadeIn(300)
        });

        $fila.find('.input-categoria').val($textoOriginalDescripcion.text());
        $fila.find('.input-detalle').val($textoOriginalDetalle.text());
    });

    // Guardar categoría
    $(document).on('click', '.btn-guardar-categoria', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let fila = $(this).closest('tr');
        let id = $(this).data('id');
        let nuevoTextoDescripcion = fila.find('.input-categoria').val();
        let nuevoTextoDetalle = fila.find('.input-detalle').val();

        Swal.fire({
            title: '¿Guardar cambios?',
            text: "¿Estás seguro de actualizar esta categoría?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': csrf },
                    url: presupuestoUrl + '/actualizar-categoria/' + id,
                    method: 'PUT',
                    data: {
                        descripcion: nuevoTextoDescripcion,
                        detalle: nuevoTextoDetalle
                    },
                    success: function (response) {
                        if (response.success) {
                            fila.find('.texto-categoria-descripcion').text(nuevoTextoDescripcion + ' ' + nuevoTextoDetalle).show();
                            fila.find('.edicion-categoria').hide();
                            Swal.fire('Guardado!', 'La categoría ha sido actualizada.', 'success');
                        } else {
                            fila.find('.texto-categoria-descripcion').show();
                            fila.find('.edicion-categoria').hide();
                            Swal.fire('Error!', 'La categoría no ha sido actualizada.', 'error');
                        }

                    },
                    error: function (xhr) {
                        Swal.fire('Error!', 'Ocurrió un error al actualizar.', 'error');
                    }
                });
            }
        });
    });

    // Editar rubro
    $(document).on('click', '.editar-rubro', function () {
        let fila = $(this).closest('tr');
        fila.find('.texto-rubro').hide();
        fila.find('.edicion-rubro').show();
    });

    // Cancelar edición rubro
    $(document).on('click', '.btn-cancelar-rubro', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let fila = $(this).closest('tr');
        fila.find('.edicion-rubro').hide();
        fila.find('.texto-rubro').show();
        fila.find('.input-rubro').val(fila.find('.texto-rubro').text())
    });

    // Guardar rubro
    $(document).on('click', '.btn-guardar-rubro', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let fila = $(this).closest('tr');
        let id = $(this).data('id');
        let nuevoTexto = fila.find('.input-rubro').val();

        Swal.fire({
            title: '¿Guardar cambios?',
            text: "¿Estás seguro de actualizar este rubro?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': csrf },
                    url: presupuestoUrl + '/actualizar-rubro/' + id,
                    method: 'PUT',
                    data: {
                        descripcion: nuevoTexto
                    },
                    success: function (response) {
                        if (response.success) {
                            fila.find('.texto-rubro').text(nuevoTexto).show();
                            fila.find('.edicion-rubro').hide();
                            Swal.fire('Guardado!', 'El rubro ha sido actualizado.', 'success');
                        } else {
                            fila.find('.texto-rubro').show();
                            fila.find('.edicion-rubro').hide();
                            Swal.fire('Error!', 'El rubro no ha sido actualizado.', 'error');
                        }

                    },
                    error: function (xhr) {
                        Swal.fire('Error!', 'Ocurrió un error al actualizar.', 'error');
                    }
                });
            }
        });
    });

});