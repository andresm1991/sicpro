$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

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
});