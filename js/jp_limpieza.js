$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    // Evento para borrar un archivo
    $('.dropify').dropify().on('dropify.beforeClear', function (event, element) {
        let id = $(this).data('id'); // Obtenemos el ID desde data-id
        let tipo = $(this).data('tipo'); // Obtenemos el ID desde data-id

        let inputTipo = $('<input>')
            .attr('type', 'hidden')
            .attr('name', tipo) // Nombre del input
            .val(id); // Valor del tipo

        // Agregar los inputs al formulario
        $('#form_proyectos').append(inputTipo);
    });


    $('input[name="fecha_inicio"]').on('change', function () {
        // Obtenemos la fecha original del input
        let fechaOriginal = $(this).val();
        let meses = $('input[name="tiempo_contratado"]').val(); // Número de meses a sumar
        // Verificamos que la fecha original no esté vacía
        if (fechaOriginal && meses) {
            // Convertimos la fecha a un objeto de fecha
            let nuevaFecha = sumarMeses(fechaOriginal, meses);
            // Puedes mostrarla en otro input o usarla como necesites
            $('input[name="fecha_fin"]').val(nuevaFecha);
        }
    });

    $('input[name="tiempo_contratado"]').on('keyup', function () {
        // Obtenemos la fecha original del input
        let fechaOriginal = $('input[name="fecha_inicio"]').val();
        let meses = $(this).val(); // Número de meses a sumar
        // Verificamos que la fecha original no esté vacía
        if (fechaOriginal && meses) {
            // Convertimos la fecha a un objeto de fecha
            let nuevaFecha = sumarMeses(fechaOriginal, meses);
            // Puedes mostrarla en otro input o usarla como necesites
            $('input[name="fecha_fin"]').val(nuevaFecha);
        }
    });

    function sumarMeses(fecha, meses) {
        // Usando moment.js para sumar meses
        return moment(fecha, 'DD-MM-YYYY').add(meses, 'months').format('DD-MM-YYYY');
    }
});