$(function() {

    let $tdSeleccionado; // Variable global para almacenar el TD clicado

   $(document).on('click', '.click-semana', function () {
    $tdSeleccionado = $(this); // Guardar referencia al TD clicado

    // Verificar si el TD ya está pintado
    if ($tdSeleccionado.hasClass('pintado')) {
        $('#modal-text').text('¿Desea despintar esta celda?');
        $('#btn-confirmar').text('Despintar').removeClass('btn-primary').addClass('btn-danger');
    } else {
        $('#modal-text').text('¿Desea pintar esta celda?');
        $('#btn-confirmar').text('Pintar').removeClass('btn-danger').addClass('btn-primary');
    }

    // Mostrar el modal
    $('#modalActividadDiasCronograma').modal({
        backdrop: 'static', // Evita que el modal se cierre al hacer clic fuera
        keyboard: false     // Evita que el modal se cierre con la tecla ESC
    });
    
    $('#modalActividadDiasCronograma').modal('show');

    return;



    let currentColor = $(this).css('background-color');
    let item = $(this).data('item');
    // modalActividadDiasCronograma

        if (currentColor === 'rgb(220, 20, 60)') { // Lightblue en RGB
            $(this).html('')
            $(this).css({'background-color': '', 'color': ''});
        } else {
            $(this).css({'background-color':'Crimson', 'color':'white', 'text-align': 'center', 'font-weight': 'bold', 'font-size': '10px'});
            $(this).text(item)
        }
   }); 

   // Agregar campo dinámico debajo del día correspondiente
   $('.add-field').on('click', function () {
    let day = $(this).data('day'); // Obtiene el día
    let container = $('#' + day + '-fields'); // Encuentra el contenedor de ese día
    
    // Crear nuevo campo con botón de eliminar
    let newField = $('<div class="form-group input-group mb-2">' +
                        '<input type="text" name="' + day + '[]" class="form-control">' +
                        '<div class="input-group-append">' +
                            '<button class="btn btn-danger btn-sm remove-field"><i class="fa-solid fa-xmark"></i></button>' +
                        '</div>' +
                    '</div>');

    container.append(newField); // Agrega el campo
});

// Eliminar campo dinámico
$(document).on('click', '.remove-field', function () {
    $(this).closest('.input-group').remove(); // Elimina el campo correspondiente
});

   $(document).on('shown.bs.modal', '.modal', function () {
    $(this).find('.select2-tag').each(function () {
        let $select = $(this);

        if ($select.data('select2')) {
            $select.select2('destroy'); // Destruir Select2 si ya está inicializado
        }

        // Obtener la configuración original almacenada en `data()`
        let originalOptions = $select.data('select2-config') || {};

        // Extender las opciones sin perder `createTag` ni `insertTag`
        let newOptions = $.extend(true, {}, originalOptions, {
            dropdownParent: $select.closest('.modal')
        });

        // Guardar la nueva configuración
        $select.data('select2-config', newOptions);

        // Inicializar Select2 con la configuración fusionada
        $select.select2(newOptions);
    });
   });
});