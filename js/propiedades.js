$(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    let maxInputs = 5;

    //$('#metros-construccion').hide();

    $('#tipo-propiedad').on('change', function () {
        let tipo = $("#tipo-propiedad option:selected").text().toLowerCase();
        if (tipo == 'casa') {
            $('#metros-construccion').show();
        } else {
            $('#metros-construccion').hide();
        }

        calcularPrecioPorMetroCuadrado();
    })

    $('#mi-ubicacion').click(function () {
        $('#loading').addClass('show');
        getLocation().then(function (coordenadas) {
            $('input[name="latitud"]').val(coordenadas.latitud);
            $('input[name="longitud"]').val(coordenadas.longitud);

            $('#ubicacion').val('ubicación obtenida');

            $('#loading').removeClass('show');
        }).catch(function (error) {
            console.error("Error al obtener la ubicación:", error);
            alert(
                "No se pudo obtener la ubicación. Asegúrate de que los permisos de ubicación estén habilitados."
            );
            $('#loading').removeClass('show');
        });
    });

    $('#area_lote, #precio_venta, #area-construccion').on('input change keyup', function () {
        calcularPrecioPorMetroCuadrado();
    });


    $('#agregar_imagen').on('click', function (e) {
        e.preventDefault();

        let total = $('#contenedor_imagenes input[type="file"]').length;
        if (total >= maxInputs) return;

        // Crea nuevo input con botón de eliminar
        const nuevoInput = $(`
            <div class="form-group col-sm-6 position-relative">
                <input type="file" name="files[]" class="dropify" data-height="100"
                    data-id="{{ $propiedad->id }}" data-tipo="archivo_orden_compra">
                <button type="button" class="btn btn-sm btn-danger btn-eliminar-imagen" title="Eliminar"
                    style="position: absolute; top: 5px; right: 5px; z-index: 10;">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `);

        $('#contenedor_imagenes .form-group.col-sm-6').last().after(nuevoInput);

        nuevoInput.find('input[type="file"]').dropify();
        actualizarContador();
    });

    // Evento delegado para eliminar inputs
    $(document).on('click', '.btn-eliminar-imagen', function () {
        const $container = $(this).closest('.form-group.col-sm-6');
        // Si el input tiene dropify, destruirlo primero
        const drEvent = $container.find('input.dropify').data('dropify');
        if (drEvent) {
            drEvent.destroy();
        }
        $container.remove();
        actualizarContador();
    });

    /**
     * Event listener para buscar propiedades
     * Este evento se activa al escribir en el campo de búsqueda
     * y realiza una petición AJAX para filtrar las propiedades.
     */
    $('#buscar-propiedad').on('keyup', function () {
        let buscar = $(this).val().toLowerCase();
        $.ajax({
            url: 'buscar-propiedad',
            type: 'GET',
            data: { text: buscar },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                $('#table-list-propiedades tbody').empty(); // Limpia la tabla antes de agregar nuevos resultados
                if (response.length === 0) {
                    $('#table-list-propiedades tbody').append('<tr><td colspan="7" class="text-center">No se encontraron propiedades</td></tr>');
                    return;
                }
                $('#table-list-propiedades tbody').append(response);

                // Inicializa los tooltips y popovers
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        });
    });

    $(document).on('click', '.eliminar-propiedad', function () {
        let id = $(this).attr('id');

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
                    url: 'eliminar-propiedad/' + id,
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    type: 'DELETE',
                    dataType: 'json',
                })
                    .done(function (data) {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message,
                            });

                            $("#table-list-propiedades #" + id).remove();

                            if ($('#table-list-propiedades tbody').children().length == 0) {
                                $('#table-list-propiedades tbody').html('<tr><td colspan = "7" class="text-center text-danger">No se encontraron datos para mostrar.</td></tr>');
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
        });
    });

    $(document).on('click', '.compartir-ubicacion', function (event) {
        event.preventDefault();
        const $boton = $(this);
        const coordenadas = $boton.data('ubicacion');
        const nombrePropiedad = $boton.attr('data-nombre');
        console.log(nombrePropiedad);
        if (!coordenadas || typeof coordenadas.lat === 'undefined' || typeof coordenadas.lng === 'undefined') {
            console.error('Los datos de ubicación son inválidos o no existen.');
            alert('Los datos de ubicación son inválidos.');
            return;
        }

        const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${coordenadas.lat},${coordenadas.lng}`;

        navigator.clipboard.writeText(googleMapsUrl).then(() => {

            Swal.fire({
                icon: 'success',
                title: '¡Copiado!',
                text: `La URL de "${nombrePropiedad}" ha sido copiada al portapapeles.`,
                timer: 2000,
                showConfirmButton: false
            });

        }).catch(err => {
            console.error('Error al intentar copiar la URL: ', err);
            alert('No se pudo copiar la URL. Por favor, inténtalo manualmente.');
        });
    });

    actualizarContador();

    function actualizarContador() {
        let total = $('#contenedor_imagenes input[type="file"]').length;
        let restantes = maxInputs - total;
        $('#imagenes_restantes').text('Puedes subir ' + restantes + ' imagen' + (restantes === 1 ? '' : 'es') + ' más');
        $('#agregar_imagen').prop('disabled', restantes <= 0);
    }

    function calcularPrecioPorMetroCuadrado() {
        let tipo = $("#tipo-propiedad option:selected").text().toLowerCase();
        var areaLote = parseFloat($('#area_lote').val()) || 0;
        var areaConstruccion = parseFloat($('#area-construccion').val()) || 0;
        var precioVenta = parsePrecio($('#precio_venta').val());
        var precioPorMetroCuadrado = 0;
        if (areaLote > 0 && precioVenta > 0) {
            if (tipo == 'casa') {
                if (areaConstruccion > 0) {
                    precioPorMetroCuadrado = (precioVenta / areaConstruccion).toFixed(2);
                } else {
                    precioPorMetroCuadrado = '';
                }
            } else {
                precioPorMetroCuadrado = (precioVenta / areaLote).toFixed(2);
            }
            $('#precio_mt2').val(precioPorMetroCuadrado);
        } else {
            $('#precio_mt2').val('');
        }
    }
});