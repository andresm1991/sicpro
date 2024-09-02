$(document).ready(function () {
    // Almacena los archivos seleccionados para cada input
    const selectedFilesMap = {};

    // Manejar la selección de múltiples archivos
    $('.multiFileInput').on('change', function (event) {
        const inputId = $(this).attr('id');
        const fileList = event.target.files;

        if (!selectedFilesMap[inputId]) {
            selectedFilesMap[inputId] = [];
        }

        for (let i = 0; i < fileList.length; i++) {
            selectedFilesMap[inputId].push(fileList[i]);
        }
        updateFileList(inputId);
    });

    // Abrir el selector de archivos al hacer clic en el contenedor
    $('.file-input-container').on('click', function (e) {
        const fileInput = $(this).find('input[type="file"]');

        // Prevenir la propagación del evento click desde el input file
        e.stopPropagation();

        // Si el clic es en el contenedor y no en el input file, abrir el selector
        if (!$(e.target).is('input[type="file"]')) {
            fileInput.trigger('click');
        }
    });

    // Manejar la eliminación de archivos en la lista
    $(document).on('click', '.remove-file', function () {
        const inputId = $(this).closest('.file-list').data('input-id');
        const fileIndex = $(this).data('index');

        selectedFilesMap[inputId].splice(fileIndex, 1);
        updateFileList(inputId);
    });

    // Actualizar la lista de archivos seleccionados
    function updateFileList(inputId) {
        const $fileListDisplay = $(`#fileList-${inputId}`);
        $fileListDisplay.empty(); // Limpiar la lista antes de añadir nuevos elementos

        selectedFilesMap[inputId].forEach(function (file, index) {
            const $listItem = $('<li></li>').text(file.name);
            const $removeButton = $('<span class="remove-file" data-index="' + index + '"><i class="fa-solid fa-trash-can"></i></span>');

            $listItem.append($removeButton);
            $fileListDisplay.append($listItem);
        });

        mostrarOcultarSinArchivos(inputId);
    }

    // Mostrar u ocultar el mensaje de "No existen archivos seleccionados"
    function mostrarOcultarSinArchivos(inputId) {
        if ($(`#fileList-actuales-${inputId}`).children('li').length == 0 && selectedFilesMap[inputId].length == 0) {
            $(`#sin-archivos-${inputId}`).css('display', 'block');
        } else {
            $(`#sin-archivos-${inputId}`).css('display', 'none');
        }
    }

    $('#form_proyectos').on('submit', function (event) {
        var form = event.target; // Puede ser $(this) para jQuery


        Object.keys(selectedFilesMap).forEach(function (inputId) {
            selectedFilesMap[inputId].forEach(function (file) {
                var fileInput = $('<input>').attr({
                    type: 'file',
                    name: 'archivos_proyecto[' + inputId + '][]'
                }).css('display', 'none')[0];

                // Asigna los archivos al nuevo input
                fileInput.files = createFileList(file);
                // Añadir el input al formulario
                $(form).append(fileInput);
            });
        });
    });

    function createFileList(file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        return dataTransfer.files;
    }

    // Manejar el clic en el botón de eliminar archivos actuales
    $('.file-list').on('click', '.remove-file-actuales', function () {
        var $fileList = $(this).closest('ul'); // Intentar con 'ul' directamente
        console.log($fileList); // Verificar que $fileList no sea null o undefined

        if ($fileList.length > 0) {
            console.log("File list ID:", $fileList.attr('id'));
            $(this).closest('li').remove();
            mostrarOcultarSinArchivosActuales($fileList);
        } else {
            console.error("No se encontró la lista de archivos correspondiente.");
        }
    });

    // Función para mostrar u ocultar el mensaje dependiendo de la lista
    function mostrarOcultarSinArchivosActuales($fileList) {
        // Verificar si el elemento tiene un ID
        var fileListId = $fileList.attr('id');
        // Si el ID no existe o no tiene el formato esperado, mostrar un mensaje en la consola
        if (!fileListId) {
            console.error("El ul no tiene un ID válido:", $fileList);
            return;
        }

        // Extraer el ID del tipo de archivo si el ID tiene el formato esperado
        var inputIdPart = fileListId.split('-')[2];

        if (!inputIdPart) {
            console.error("El ID del ul no tiene el formato esperado:", fileListId);
            return;
        }

        // Construir el ID del span de "No existen archivos seleccionados"
        var $sinArchivos = $('#sin-archivos-' + inputIdPart);

        console.log("Mostrando u ocultando el mensaje para:", $sinArchivos.attr('id'));

        // Mostrar u ocultar el mensaje dependiendo del contenido de la lista
        if ($fileList.children('li').length === 0) {
            $sinArchivos.css('display', 'block');
        } else {
            $sinArchivos.css('display', 'none');
        }
    }
});
