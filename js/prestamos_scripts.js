import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var reloadPage = false;

    $('#modalPrestamo').on('shown.bs.modal', function (e) {
        $('.select2-basic-single').select2({
            width: '100%',
            dropdownParent: $('#modalPrestamo'), //ID del modal
            allowClear: false,
            placeholder: function () {
                $(this).data('placeholder');
            },
        });

        $.ajax({
            url: '/proveedores',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'categoria_proveedor': 5 },
            beforeSend: function () {

            },
            success: function (response) {
                $('#proveedores').empty().append('<option></option>');
                $('#estados').empty().append('<option></option>');
                $.each(response.proveedores, function (key, value) {
                    $('#proveedores').append(`<option value="${key}">${value}</option>`);
                });

                $.each(response.estados, function (key, value) {
                    $('#estados').append(`<option value="${key}">${value}</option>`);
                });
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            // Habilitar el botón y ocultar el spinner
            $button.prop('disabled', false);
            $spinner.addClass('d-none');
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    $("#guardar").on('click', function () {
        var form = $("#form_prestamo");
        var data = getFormData(form);

        var valid = true;

        $('select[name=proveedor], select[name=estado], input[name=monto], input[name=plazo], input[name=interes]').removeClass('error-border');
        $('.select2-basic-single').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        if (data.proveedor == "") {
            var valid = false;
            $('select[name=proveedor]').next('.select2-basic-single').find('.select2-selection').addClass('error-border');
            $('select[name=proveedor]').parent().append('<span class="error-message">Seleccione el trabajador.</span>');
        }

        if (data.estado == "") {
            var valid = false;
            $('select[name=estado]').next('.select2-basic-single').find('.select2-selection').addClass('error-border');
            $('select[name=estado]').parent().append('<span class="error-message">Seleccione opción.</span>');
        }

        if (data.monto == "") {
            var valid = false;
            $('input:text[name=monto]').addClass('error-border');
            $('input:text[name=monto]').parent().append('<span class="error-message">Ingrese Monto.</span>');
        }

        if (data.plazo == "") {
            var valid = false;
            $('input:text[name=plazo]').addClass('error-border');
            $('input:text[name=plazo]').parent().append('<span class="error-message">Ingrese Plazo.</span>');
        }

        if (!valid) return;

        $.ajax({
            url: 'prestamos/nuevo',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {

            },
            success: function (response) {
                if (response.success) {
                    $('tbody').html(response.prestamos);

                    $('#message').html('<div class="alert alert-success alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-check"></i> ' + response.mensaje + '</h5>' +
                        '</div>');
                } else {
                    $('#message').html('<div class="alert alert-danger alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-ban"></i> ' + response.mensaje + '</h5>' +
                        '</div>');
                }
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    // Capturar el clic en "Registrar Pago"
    $(document).on('click', '.registrar-pago', function () {

        let montoProgramado = $(this).data('monto-programado');
        let pago_id = $(this).data('pago');

        $('input:text[name=monto_programado]').val(montoProgramado);
        $('input:hidden[name=pago_id]').val(pago_id);
        $.ajax({
            url: '/forma-pago-prestamo',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            beforeSend: function () {

            },
            success: function (response) {
                $('#forma_pago').empty().append('<option></option>');
                $('#estados').empty().append('<option></option>');
                $.each(response, function (key, value) {
                    $('#forma_pago').append(`<option value="${value.id}">${value.text}</option>`);
                });


                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            // Habilitar el botón y ocultar el spinner
            $button.prop('disabled', false);
            $spinner.addClass('d-none');
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });

        $('#modalPago').modal('show');
    });

    // Capturar el clic en "Posponer Pago" (opcional)
    $(document).on('click', '.posponer-pago', function () {
        $('[data-toggle="popover"]').popover('hide');
        alert('Pago pospuesto');
    });

    $(document).on('click', '#registrar_pago', function () {
        var form = $("#form_pago");
        var data = getFormData(form);

        var valid = true;

        $('select[name=forma_pago], input[name=monto_programado], input[name=monto_pagado]').removeClass('error-border');
        $('.select2-basic-single').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();  // Elimina los mensajes de error anteriores

        if (data.forma_pago == "") {
            var valid = false;
            $('select[name=forma_pago]').next('.select2-basic-single').find('.select2-selection').addClass('error-border');
            $('select[name=forma_pago]').parent().append('<span class="error-message">Seleccione opción.</span>');
        }

        if (data.monto_pagado == "") {
            var valid = false;
            $('input:text[name=monto_pagado]').addClass('error-border');
            $('input:text[name=monto_pagado]').parent().append('<span class="error-message">Ingrese Monto.</span>');
        }

        if (!valid) return;

        $.ajax({
            url: '/administrativo/prestamos/pago/' + data.pago_id,
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'PUT',
            data: data,
            beforeSend: function () {
                reloadPage = false;
            },
            success: function (response) {
                if (response.success) {
                    reloadPage = true;

                    $('#message').html('<div class="alert alert-success alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-check"></i> ' + response.mensaje + '</h5>' +
                        '</div>');
                } else {
                    reloadPage = false;
                    $('#message').html('<div class="alert alert-danger alert-dismissible">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
                        '<h5><i class="icon fas fa-ban"></i> ' + response.mensaje + '</h5>' +
                        '</div>');
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
            reloadPage = false;
        });
    });


    $('#modalPago').on('hidden.bs.modal', function (e) {
        if (reloadPage) {
            location.reload(); // Recarga la página
        }
        $('#message').hide();
        $('.input_errors').remove();
        $('select[name=forma_pago], input[name=monto_programado], input[name=monto_pagado]').removeClass('error-border');
        $('.select2-basic-single').removeClass('error-border');  // Remover borde rojo en select2
        $('.error-message').remove();

    });

});