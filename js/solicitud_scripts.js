$(function (){
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('select[name=tipo_solicitud]').on('change', function(){
        let selectedOption = $(this).find('option:selected').text();
        if(selectedOption === 'Reposición de Ausencia'){
            $('#check_recuperable').attr('disabled', true);
        }else{
            $('#check_recuperable').attr('disabled', false);
        }
    });

    $('input[name=fecha_desde], input[name=fecha_hasta]').datepicker().on('changeDate', function () {
        console.log('Fecha cambiada');
        calcularYMostrarTiempoLaboral();
    });

    // Escuchar cambios en los campos de hora usando el evento 'changeTime' del plugin Timepicker
    $('#hora_inicio, #hora_fin').on('change', function () {
        console.log('entro '+ $(this).val())
        calcularYMostrarTiempoLaboral();
    });

    /**
     * Buscar solcitud
     * @param String
     * return JSON
    */
    $('input:text[name=solicitud_search]').on('keyup', function () {
        var $value = $(this).val();
        $.ajax({
            url: 'solicitudes/buscar',
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

    // Función para calcular y mostrar el tiempo laboral
    function calcularYMostrarTiempoLaboral() {
        let fechaDesde = $('input[name=fecha_desde]').val(); // d-m-Y
        let horaDesde = $('input[name=hora_inicio]').val();  // H:i
        let fechaHasta = $('input[name=fecha_hasta]').val(); // d-m-Y
        let horaHasta = $('input[name=hora_fin]').val();     // H:i

        // Validar que todos los campos tengan valores
        if (!fechaDesde || !horaDesde || !fechaHasta || !horaHasta) {
            $('#resultado').text('00:00');
            return;
        }

        // Calcular el tiempo laboral
        let tiempoLaboral = calcularTiempoLaboral(fechaDesde, horaDesde, fechaHasta, horaHasta);

        // Mostrar el resultado
        $('#resultado').text(tiempoLaboral);
    }

    
});