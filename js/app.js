
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
        startDate: new Date(),
        todayHighlight: true,

    });

    $('.select2-basic-single').select2({
        placeholder: function () {
            $(this).data('placeholder');
        },
        allowClear: true
    });

    /// Solo numeros
    $(document).on('input', ".solo-numeros", function (evt) {
        // Allow only numbers.
        $(this).val(jQuery(this).val().replace(/[^0-9]/g, ''));
    });

    $('#calificacion').on('focus', function () {
        if ($(this).val() == 0) {
            $(this).val('');
        }
    });

    $('#calificacion').on('blur', function () {
        if ($(this).val() === '') {
            $(this).val(0);
        }
    });

    $('#calificacion').on('input', function () {
        const value = $(this).val();
        // Remover cualquier número fuera del rango 1-5
        if (value < 1 || value > 5) {
            $(this).val('');
        }
    });

    $('#calificacion').on('keypress', function (e) {
        const char = String.fromCharCode(e.which);
        // Permitir solo dígitos 1-5
        if (!/[1-5]/.test(char)) {
            e.preventDefault();
        }
    });

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


    $('#form_proyectos').on('submit', function (event) {
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
    });

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

        mostrarOcultarSinArchivos();
    }

    function createFileList(file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        return dataTransfer.files;
    }

    function mostrarOcultarSinArchivos() {
        if ($('#fileList-actuales').children('li').length == 0 && selectedFiles.length == 0) {
            $('#sin-archivos').css('display', 'block');
        } else {
            $('#sin-archivos').css('display', 'none');
        }
    }

    function imageLoaded(img) {
        img.parentElement.classList.add('loaded');
    }

});