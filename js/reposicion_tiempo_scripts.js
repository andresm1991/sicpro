$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var btnGuardar = $("#btn-guardar");


    $('select[name=user]').on('change', function () {
        let $selectedOption = $(this).find('option:selected').val();
        $.ajax({
            url: base_url + '/tiempo-permisos',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'user_id': $selectedOption },
            beforeSend: function () {
                // Mostrar el loading
                $('#loading').addClass('show');
            },
            success: function (response) {
                $('#tiempo_pendiente').val(response.tiempo_total);
                calcularYMostrarTiempoLaboral()

            },
            complete: function () {
                // Ocultar el loading cuando termine la petición
                $('#loading').removeClass('show');
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            Swal.fire(
                'Error Inesperado!',
                'No se pudo realizar la acción solicitada, comuníquese con el administrador del sistema.',
                'error'
            )
        });
    });

    if ($('select[name=user]').val()) {
        $('select[name=user]').trigger('change');
    }

    /**
     * Buscar solcitud
     * @param String
     * return JSON
    */
    $('input:text[name=reposicion_search]').on('keyup', function () {
        var $value = $(this).val();
        $.ajax({
            url: 'reposicion/buscar',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'text': $value },
            beforeSend: function () {
            },
            success: function (data) {
                $('tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });

    $('#hora_inicio, #hora_fin').on('change', function () {
        console.log($(this).val());
        calcularYMostrarTiempoLaboral();
    });

    // Forzar la actualización del timepicker para valores predefinidos
    $('.timepicker').each(function (index) {
        var initialValue = $(this).val(); // Obtener el valor predefinido del campo
        if (initialValue) {
            if (index == 0) {
                $('#hora_inicio').timepicker('setTime', initialValue); // Establecer el valor en el timepicker
            } else {
                $('#hora_fin').timepicker('setTime', initialValue); // Establecer el valor en el timepicker
            }

        }
    });
    // Validar los campos en tiempo real
    function calcularYMostrarTiempoLaboral() {
        let mensaje = $("#mensaje");
        let tiempoResposicion = $("#tiempo_reposicion");
        let botonEnviar = $("#btn-guardar");
        let horaInicio = $("#hora_inicio").val();
        let horaFin = $("#hora_fin").val();

        // Limpiar mensaje inicialmente
        mensaje.text("");
        tiempoResposicion.html("0 horas y 0 minutos");
        botonEnviar.prop("disabled", true);

        // Validar que ambos campos estén completos
        if (!horaInicio || !horaFin) {
            return;
        }

        // Validar que ambas horas estén dentro del rango permitido
        if (!validarRangoHora(horaInicio)) {
            mensaje.text("La hora desde debe estar entre 08:00 y 18:00.");
            return;
        }
        if (!validarRangoHora(horaFin)) {
            mensaje.text("La hora hasta debe estar entre 08:00 y 18:00.");
            return;
        }

        // Convertir las horas a minutos para compararlas
        let [inicioHoras, inicioMinutos] = horaInicio.split(":").map(Number);
        let [finHoras, finMinutos] = horaFin.split(":").map(Number);
        let minutosInicio = inicioHoras * 60 + inicioMinutos;
        let minutosFin = finHoras * 60 + finMinutos;

        // Validar que la hora_fin sea mayor o igual que la hora_inicio
        if (minutosFin < minutosInicio) {
            mensaje.text("La hora hasta debe ser mayor o igual que la hora desde.");
            return;
        }

        // Calcular y mostrar el tiempo total
        let tiempoCalculado = calcularTiempoTotal(horaInicio, horaFin);

        // Obtener el tiempo pendiente de reposición
        let tiempoPendienteValue = $("#tiempo_pendiente").val();
        let tiempoPendiente = extraerHorasYMinutos(tiempoPendienteValue);

        if (!tiempoPendiente) {
            mensaje.text("El formato del tiempo pendiente no es válido.");
            return;
        }

        // Extraer horas y minutos del tiempo calculado
        let tiempoReposicion = extraerHorasYMinutos(tiempoCalculado);

        if (!tiempoReposicion) {
            mensaje.text("El formato del tiempo de reposición no es válido.");
            return;
        }

        // Convertir ambos tiempos a minutos
        let minutosPendiente = tiempoPendiente.horas * 60 + tiempoPendiente.minutos;
        let minutosReposicion = tiempoReposicion.horas * 60 + tiempoReposicion.minutos;
        console.log(tiempoPendienteValue)
        console.log('miutos pendientes: ' + minutosPendiente)
        console.log('minutos reposicion' + minutosReposicion)
        // Validar que el tiempo de reposición no sea mayor al tiempo pendiente
        if (minutosReposicion > minutosPendiente) {
            mensaje.text("El tiempo de reposición no puede ser mayor al tiempo pendiente de reposición.");
            return;
        }

        // Mostrar el tiempo total si todo está correcto
        tiempoResposicion.html(tiempoCalculado);
        // Habilitar el botón de envío si todas las validaciones pasan
        botonEnviar.prop("disabled", false);
    };

    // Función para validar una hora dentro del rango permitido
    function validarRangoHora(hora) {
        let [horas, minutos] = hora.split(":").map(Number);
        let minutosTotales = horas * 60 + minutos;
        let minHora = 8 * 60; // 08:00 en minutos
        let maxHora = 18 * 60; // 18:00 en minutos
        return minutosTotales >= minHora && minutosTotales <= maxHora;
    }

    // Calcular el tiempo total entre hora_inicio y hora_fin
    function calcularTiempoTotal(horaInicio, horaFin) {
        let [inicioHoras, inicioMinutos] = horaInicio.split(":").map(Number);
        let [finHoras, finMinutos] = horaFin.split(":").map(Number);
        let minutosInicio = inicioHoras * 60 + inicioMinutos;
        let minutosFin = finHoras * 60 + finMinutos;

        // Calcular la diferencia en minutos
        let diferenciaMinutos = minutosFin - minutosInicio;

        // Convertir a horas y minutos
        let horas = Math.floor(diferenciaMinutos / 60);
        let minutos = diferenciaMinutos % 60;

        // Formatear el resultado como "H horas y M minutos"
        return `${horas} horas y ${minutos} minutos`;
    }

    // Extraer horas y minutos de una cadena de texto
    function extraerHorasYMinutos(texto) {
        let regex = /(\d+)\s*horas?\s*y\s*(\d+)\s*minutos?/;
        let match = texto.match(regex);

        if (match) {
            let horas = parseInt(match[1], 10);
            let minutos = parseInt(match[2], 10);
            return { horas, minutos };
        }

        return null; // Si el formato no coincide, devuelve null
    }

    // Prevenir el envío del formulario si hay errores
    $("#form_reposicion").on("submit", function (event) {
        let mensaje = $("#mensaje").text().trim();
        if (mensaje) {
            event.preventDefault(); // Evitar el envío si hay mensajes de error
            alert("Por favor, corrija los errores antes de enviar el formulario.");
        }
    });
});