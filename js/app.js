
let profile = document.querySelector('.profile');
let menu = document.querySelector('.menu');

profile.onclick = function () {
    menu.classList.toggle('active');
}

document.onclick = function (event) {
    // Verifica si el clic fue fuera del profile y del menu
    if (!profile.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.remove('active');
    }
}


// Funcion para cargar bancos dentro del select
function cargarBancos() {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var $select = $('#select-bancos');

    // Agrega una opción temporal con el mensaje de espera
    $select.empty(); // Vacía el select
    $select.append($('<option disabled></option>').attr('value', '').text('Cargando datos, por favor espera...'));

    // Refresca el selectpicker para mostrar el mensaje
    $select.selectpicker('refresh');

    // Realiza la llamada AJAX para obtener los datos
    $.ajax({
        url: '/bancos',
        headers: { 'X-CSRF-TOKEN': csrf },
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            $select.empty(); // Vacía el select nuevamente
            $.each(response, function (index, item) {
                $select.append($('<option></option>').attr('value', item.id).text(item.text));
            });
            $select.selectpicker('refresh'); // Refresca el selectpicker
        },
        error: function () {
            // En caso de error, puedes mantener el mensaje o mostrar uno de error
            $select.empty(); // Vacía el select
            $select.append($('<option class="text-danger" disabled></option>').attr('value', '').text('Error al cargar los datos.'));
            $select.selectpicker('refresh');
        }
    });
}

// Funcion para cargar tipo de cuentas dentro del select
function cargarTipoCuentas() {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var $select = $('#select-tipo-cuentas');

    // Agrega una opción temporal con el mensaje de espera
    $select.empty(); // Vacía el select
    $select.append($('<option disabled></option>').attr('value', '').text('Cargando datos, por favor espera...'));

    // Refresca el selectpicker para mostrar el mensaje
    $select.selectpicker('refresh');

    // Realiza la llamada AJAX para obtener los datos
    $.ajax({
        url: '/tipo-cuenta',
        headers: { 'X-CSRF-TOKEN': csrf },
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            $select.empty(); // Vacía el select nuevamente
            $.each(response, function (index, item) {
                $select.append($('<option></option>').attr('value', item.id).text(item.text));
            });
            $select.selectpicker('refresh'); // Refresca el selectpicker
        },
        error: function () {
            // En caso de error, puedes mantener el mensaje o mostrar uno de error
            $select.empty(); // Vacía el select
            $select.append($('<option class="text-danger" disabled></option>').attr('value', '').text('Error al cargar los datos.'));
            $select.selectpicker('refresh');
        }
    });
}


$(function () {
    $('[data-toggle="popover"]').popover({ html: true });
    $('[data-toggle="tooltip"]').tooltip({ html: true });

    $('.datepicker').datepicker({
        language: "es",
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayBtn: "linked",
        //startDate: new Date(),
        todayHighlight: true,

    });

    $('.datepicker-no-back').datepicker({
        language: "es",
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayBtn: "linked",
        startDate: new Date(),
        todayHighlight: true,

    });
    // Date picker para bloquear dias
    $('.datepicker-2').datepicker({
        language: "es",
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayBtn: "linked",
        todayHighlight: true,
        endDate: new Date(),
        //beforeShowDay: habilitarFechas,

    });

    $('.select2-basic-single').select2({
        placeholder: function () {
            $(this).data('placeholder');
        },
        allowClear: false,
    });

    $('.select2-multiple').select2({
        minimumResultsForSearch: Infinity,
        placeholder: function () {
            $(this).data('placeholder');
        },
        allowClear: false,
        tags: true, // Permite agregar nuevas opciones
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

    // Input mask
    $('.input-double').inputmask({
        alias: 'decimal',  // Usar el alias "decimal"
        radixPoint: ".",   // Definir el punto decimal
        groupSeparator: ",",  // Separador de miles (opcional)
        digits: 2,         // Número de dígitos decimales
        autoGroup: true,   // Agrupar los miles
        rightAlign: false, // Alinear a la izquierda
        allowMinus: false,   // Permitir números negativos
        placeholder: $('.input-double').data('placeholder')
    });

    $('.input-enteros').inputmask({
        alias: 'numeric',
        rightAlign: false,      // No alinear a la derecha
        allowMinus: false,      // No permitir números negativos
        digits: 0,              // No permitir decimales
        min: 0,                 // Valor mínimo (0 o positivo)
        max: undefined,         // Puedes establecer un valor máximo si lo deseas
        integerDigits: undefined, // Número máximo de dígitos permitidos en el entero (opcional)
        placeholder: "",         // Dejar vacío el placeholder si lo deseas
        autoUnmask: true         // Para que el valor sea guardado sin el formato de máscara
    });

    /// Solo numeros
    $(document).on('input', ".solo-numeros", function (evt) {
        // Allow only numbers.
        $(this).val(jQuery(this).val().replace(/[^0-9]/g, ''));
    });

    $('#calificacion').on('input', function () {
        let value = $(this).val();
        // Remueve cualquier caracter no numérico
        value = value.replace(/[^0-9]/g, '');
        // Si el número es mayor a 10, lo corta a 10
        if (value > 10) {
            value = '10';
        }

        $(this).val(value); // Establece el valor validado
    });

    /* $('#calificacion').on('keypress', function (e) {
         const char = String.fromCharCode(e.which);
         // Permitir solo dígitos 1-5
         if (!/[1-10]/.test(char)) {
             e.preventDefault();
         }
     });*/

    $('.not_blank_space').on('keypress', function (event) {
        if (event.which === 32) {
            event.preventDefault();
        }
    });

    $('.not_blank_space').on('input', function () {
        $(this).val(function (_, val) {
            return val.replace(/\s/g, '');
        });
    });

    /// Files Input
    const selectedFiles = [];

    $('#singleFileInput').on('change', function (event) {
        var file = event.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-portada').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    /// Multiple select files
    $('#fileInput').on('change', function (event) {
        const fileList = event.target.files;
        for (let i = 0; i < fileList.length; i++) {
            selectedFiles.push(fileList[i]);
        }
        updateFileList();
    });

    $(document).on('input', '.auto-ajustable', function () {
        // Ajustar el ancho del input en función del contenido
        this.style.width = ((this.value.length + 1) * 8) + 'px'; // Ajusta el multiplicador si es necesario
    });


    /*$('#form_proyectos').on('submit', function (event) {
        var form = event.target; // Puede ser $(this) para jQuery
        selectedFiles.forEach(function (file) {
            var fileInput = $('<input>').attr({
                type: 'file',
                name: 'archivos[]'
            }).css('display', 'none')[0];

            // Asigna los archivos al nuevo input
            fileInput.files = createFileList(file);

            // Añadir el input al formulario
            $(form).append(fileInput);
        });
    });

    $('#fileList-actuales').on('click', '.remove-file-actuales', function () {
        // Eliminar el elemento li padre del icono de basura
        $(this).closest('li').remove();
        mostrarOcultarSinArchivos();
    });*/

    function updateFileList() {
        var $fileListDisplay = $('#fileList');

        $fileListDisplay.empty(); // Limpiar la lista antes de añadir nuevos elementos

        selectedFiles.forEach(function (file, index) {
            var $listItem = $('<li></li>').text(file.name);
            var $removeButton = $('<span class="remove-file"><i class="fa-solid fa-trash-can"></i></span>');

            // Añadir evento de clic al botón de eliminar
            $removeButton.on('click', function () {
                selectedFiles.splice(index, 1);
                updateFileList();
            });

            $listItem.append($removeButton);
            $fileListDisplay.append($listItem);


        });
    }

    /*function createFileList(file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        return dataTransfer.files;
    }*/

    function imageLoaded(img) {
        img.parentElement.classList.add('loaded');
    }

    // Función para habilitar la fecha actual y la fecha anterior (de lunes a viernes)
    function habilitarFechas(date) {
        var today = new Date(); // Obtener la fecha actual

        // Obtener el día de la semana (0 = Domingo, 1 = Lunes, ..., 6 = Sábado)
        var day = today.getDay();

        // Para comparar solo la fecha sin hora
        today.setHours(0, 0, 0, 0);
        date.setHours(0, 0, 0, 0);

        // Permitir siempre la fecha actual
        if (date.getTime() === today.getTime()) {
            return true;
        }

        // Habilitar la fecha anterior de lunes a viernes
        var fechaAnterior;
        if (day === 1) { // Si es lunes, habilitar el viernes anterior
            fechaAnterior = new Date(today);
            fechaAnterior.setDate(today.getDate() - 3); // Restar 3 días para obtener el viernes
        } else if (day >= 2 && day <= 5) { // De martes a viernes, habilitar solo el día anterior
            fechaAnterior = new Date(today);
            fechaAnterior.setDate(today.getDate() - 1);
        }

        // Permitir solo la fecha anterior calculada
        if (date.getTime() === fechaAnterior.getTime()) {
            return true;
        }

        // Deshabilitar todas las demás fechas
        return false;
    }

});