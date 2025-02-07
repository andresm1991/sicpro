$(function() {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    let $tdSeleccionado; // Variable global para almacenar el TD clicado

    // Configuración base para Select2
    const select2TagConfig = {
        width: '100%',
        allowClear: false, // Permite limpiar la selección
        tags: true, // Permite agregar nuevas opciones escribiendo
        placeholder: function () {
            return $(this).data('placeholder');
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') {
                return null;
            }
            return {
                id: term,
                text: term,
                newTag: true
            };
        },
        insertTag: function (data, tag) {
            data.unshift(tag); // Inserta la nueva opción al principio
        }
    };

   $(document).on('click', '.click-semana', function () {
    $tdSeleccionado = $(this); // Guardar referencia al TD clicado
    getActividadesCronograma($('.actividades'));
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
    
    // Crear un nuevo <select> con botón de eliminar
    let newField = $('<div>', {
        class: 'form-group input-group mb-2',
        html: [
            $('<select>', {
                name: day + '[]',
                class: 'form-control actividades'
            }),
            $('<div>', {
                class: 'input-group-append',
                html: $('<button>', {
                    class: 'btn btn-danger btn-sm remove-field',
                    html: '<i class="fa-solid fa-xmark"></i>'
                })
            })
        ]
    });

    container.append(newField); // Agrega el campo
    // Obtener el nuevo <select> creado
    let $newSelect = container.find('.actividades').last();

    // Inicializar Select2 con la misma configuración base
    $newSelect.select2($.extend(true, {}, select2TagConfig, {
        dropdownParent: container.closest('.modal') // Para que funcione dentro de modales
    }));

    // Cargar opciones dinámicas para el nuevo <select>
    getActividadesCronograma($newSelect);
});

// Eliminar campo dinámico
$(document).on('click', '.remove-field', function () {
    $(this).closest('.input-group').remove(); // Elimina el campo correspondiente
});

   $(document).on('shown.bs.modal', '.modal', function () {
    $(this).find('.actividades').each(function () {
        let $select = $(this);

        // Destruir Select2 si ya está inicializado
        if ($select.data('select2')) {
            $select.select2('destroy');
        }

        // Inicializar Select2 con la configuración base y dropdownParent
        $select.select2($.extend(true, {}, select2TagConfig, {
            dropdownParent: $(this).closest('.modal') // Para que funcione dentro de modales
        }));

        // Cargar opciones dinámicas
        getActividadesCronograma($select);
    });
   });

   var getActividadesCronograma = function($select) {
    $.ajax({
        url: '/actividades-cronograma',
        headers: { 'X-CSRF-TOKEN': csrf },
        type: 'GET',
        success: function (response) {
            // Limpiar el <select>
            $select.empty();

            // Agregar una opción vacía para el placeholder
            $select.append(new Option('', '', false, false));

            // Iterar sobre las actividades y crear nuevas opciones
            $.each(response.actividades, function (index, actividad) {
                let option = new Option(actividad, index, false, false);
                $select.append(option);
            });

            // Desencadenar el evento 'change' para actualizar Select2
            $select.trigger('change');
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        switch (jqXHR.status) {
            case 419: // ERROR EXPIRATE SESSION
                window.location = '/';
                break;

            default:
                var errors = JSON.parse(jqXHR.responseText);
                Swal.fire('Error!',errors,'error');
        }
    });
   };
});