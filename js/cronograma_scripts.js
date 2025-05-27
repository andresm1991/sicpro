import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $(document).on('click', '#guardar', function () {
        var form = $("#form_rubro_cronograma");
        var data = getFormData(form);
        $.ajax({
            url: 'guardar/rubro-cronograma',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#modal-overlay').show();
            },
            success: function (response) {

                Swal.fire({
                    icon: response.success ? "success" : "error",
                    text: response.message,
                    confirmButtonText: 'Aceptar',
                }).then((result) => {
                    if (response.success) {
                        location.reload();
                    }
                });


                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            complete: function () {
                $('#modal-overlay').hide();
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            switch (jqXHR.status) {
                case 422: // ERROR INPUT VALIDATE

                    break;

                case 419: // ERROR EXPIRATE SESSION
                    window.location = '/';
                    break;

                default:
                    var errors = JSON.parse(jqXHR.responseText);
                    Swal.fire(
                        'Ups.!',
                        'Algo salió mal, por favor vuelva a intentarlo.',
                        'error'
                    )
                    console.log(errors)
            }
        });
    });

    $('.editar-rubro').on('click', function () {
        console.log('Editar Rubro');
        // Obtener el número de semana desde el atributo 'data-semana'
        let semana = $(this).data('semana');
        let rubro = $(this).data('rubro');
        let dias = $(this).data('dias');


        $('#titleModal').html('Editar Actividades');
        $('select[name=rubro_cronograma]').val(rubro).trigger('change');
        $('select[name=semana]').val(semana).trigger('change');
        if (dias) {
            let diasArray = dias.split(", ").map(dia => dia.trim()); // Convertir a array y limpiar espacios

            // Recorrer los días y marcar los checkboxes correspondientes
            diasArray.forEach(dia => {
                $("#check_" + dia).prop("checked", true);
            });
        }
        console.log(dias);

        $('#modalRubroCronograma').modal({
            backdrop: 'static', // No permite cerrar el modal al hacer clic fuera
            keyboard: false     // No permite cerrar el modal usando la tecla ESC
        }).modal('show');
    });

    // Capturar clic en las columnas con la clase 'editar-semana'
    $('.editar-semana').on('click', function () {
        // Obtener el número de semana desde el atributo 'data-semana'
        let semana = $(this).data('semana');

        // Crear un objeto para almacenar los datos de la semana
        let datosSemana = {};

        // Iterar sobre las filas de la tabla para recopilar los datos de la semana
        $('#table-rubros-cronograma tbody tr').each(function () {
            let rubro = $(this).find('td:eq(1)').text().trim(); // Nombre del rubro
            let celdaSemana = $(this).find('td:eq(' + (semana + 1) + ')'); // Celda correspondiente a la semana
            let dias = celdaSemana.data('dias'); // Obtener los días desde el atributo 'data-dias'

            if (dias) {
                // Almacenar los datos del rubro para esta semana
                datosSemana[rubro] = dias;
            }

        });

        // Mostrar los datos en la consola (o puedes usarlos como desees)
        console.log(`Datos de la semana ${semana}:`, datosSemana);

        // Ejemplo: Mostrar los datos en un modal o alerta
        alert(`Datos de la semana ${semana}:\n${JSON.stringify(datosSemana, null, 2)}`);
    });


    // Inicializar Select2 dentro del modal
    $(document).on('shown.bs.modal', '#modalRubroCronograma', function () {
        $(this).find('.select2-tag').each(function () {
            let $select = $(this);

            // Destruir Select2 si ya está inicializado
            if ($select.data('select2')) {
                $select.select2('destroy');
            }

            // Obtener la configuración original almacenada en `data()`
            let originalOptions = $select.data('select2-config') || {};

            // Extender las opciones sin perder `createTag` ni `insertTag`
            let newOptions = $.extend(true, {}, originalOptions, {
                dropdownParent: $select.closest('.modal'),
                placeholder: $select.data('placeholder') || 'Seleccione una opción',
                allowClear: false
            });

            // Guardar la nueva configuración
            $select.data('select2-config', newOptions);

            // Inicializar Select2 con la configuración fusionada
            $select.select2(newOptions);
        });

        $(this).find('.select2-basic-single').each(function () {
            let $select = $(this);

            // Destruir Select2 si ya está inicializado
            if ($select.data('select2')) {
                $select.select2('destroy');
            }

            // Obtener la configuración original almacenada en `data()`
            let originalOptions = $select.data('select2-config') || {};

            // Extender las opciones sin perder `createTag` ni `insertTag`
            let newOptions = $.extend(true, {}, originalOptions, {
                dropdownParent: $select.closest('.modal'),
                placeholder: $select.data('placeholder') || 'Seleccione una opción',
                allowClear: false
            });

            // Guardar la nueva configuración
            $select.data('select2-config', newOptions);

            // Inicializar Select2 con la configuración fusionada
            $select.select2(newOptions);
        });
    });
    // Cerrar el modal
    $('#modalRubroCronograma').on('hidden.bs.modal', function () {
        $('#titleModal').html('Agregar Actividades');
        // Restablecer el formulario completo
        $('#form_rubro_cronograma')[0].reset();

        // Restablecer los select2
        $('.select2-tag, .select2-basic-single').val(null).trigger('change');

        // Desmarcar los checkboxes manualmente
        $('input[type="checkbox"]').prop('checked', false);
    });


    $(document).on('click', '.click-semana', function () {
        window.location = 'semana/' + $(this).data('semana') + '/rubro/' + $(this).data('rubro');
    });

    // Evento clic para los botones "Agregar actividad"
    $('.add-actividad').on('click', function () {
        const day = $(this).data('day'); // Obtener el día asociado al botón
        const $select = $(`#${day}-fields .actividades`); // Encontrar el <select> correspondiente
        const selectedValue = $select.val(); // Obtener el valor seleccionado
        const selectedText = $select.find('option:selected').text(); // Obtener el texto seleccionado

        // Validar que se haya seleccionado una opción
        if (!selectedValue) {
            alert('Por favor, seleccione una actividad.');
            return;
        }

        // Encontrar el contenedor donde se mostrarán las actividades seleccionadas
        const $container = $(this).closest('.day-item').find('.actividades-seleccionadas');

        // Verificar si la actividad ya está agregada (evitar duplicados)
        if ($container.find(`[data-value="${selectedValue}"]`).length > 0) {
            alert('Esta actividad ya ha sido agregada.');
            return;
        }

        // Crear un nuevo elemento para mostrar la actividad
        const newActivity = $('<div>', {
            class: 'd-flex align-items-center bg-dark text-white rounded p-2 mr-2 mb-2',
            'data-value': selectedValue,
            css: { 'max-width': '100%' }, // Limita el ancho máximo del contenedor
            html: [
                $('<span>', {
                    class: 'flex-grow-1 text-wrap',
                    text: selectedText,
                    css: {
                        'word-break': 'break-word', // Divide palabras largas
                        'overflow-wrap': 'break-word' // Maneja palabras extremadamente largas
                    }
                }),
                $('<a>', {
                    href: 'javascript:void(0);',
                    class: 'btn btn-sm btn-danger remove-activity ml-auto',
                    html: '<i class="fa-solid fa-xmark"></i>'
                }),
                $('<input>', {
                    type: 'hidden',
                    name: `${day}[]`,
                    value: selectedValue
                })
            ]
        });

        // Añadir la nueva actividad al contenedor
        $container.append(newActivity);
    });

    // Evento click para eliminar actividades (funciona para elementos dinámicos y estáticos)
    $(document).on('click', '.remove-activity', function () {
        const $activityDiv = $(this).closest('.d-flex'); // Encuentra el contenedor de la actividad
        const day = $activityDiv.closest('.day-item').find('.add-actividad').data('day'); // Obtiene el día asociado

        $activityDiv.remove(); // Elimina el contenedor de la actividad
        updateDynamicFields(day); // Actualiza los campos dinámicos
    });

    // Función para actualizar los campos dinámicos
    function updateDynamicFields(day) {
        const $container = $(`.day-item [data-day="${day}"]`).closest('.day-item').find('.actividades-seleccionadas');
        $container.find('input[type="hidden"]').remove(); // Limpiar campos antiguos

        $container.find('[data-value]').each(function () {
            const value = $(this).data('value');
            $(this).append(
                $('<input>', {
                    type: 'hidden',
                    name: `${day}[]`,
                    value: value
                })
            );
        });
    }

    $(document).on('click', '.editar-actividad', function () {
        // Aquí va tu lógica
        let id = $(this).attr('id');
        let nombre = $(this).text().trim();
        Swal.fire({
            title: 'Informacion de la Actividad',
            input: 'text',
            inputValue: nombre,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            didOpen: () => {

            }
        }).then((result) => {
            if (result.isConfirmed) {
                var value = parsePrecio(result.value) || '0.00'
                // Actualizar el valor en el campo
                $span.text('$ ' + value);
                $input.val(value);

                // Recalcular valores dependientes
                recalculateDependentValues($tr, colName);
            }
        });
    });
});