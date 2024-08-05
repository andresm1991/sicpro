$(document).ready(function () {
    var fieldCount = 1;

    $('#addFieldButton').click(function () {
        console.log('asasas');
        fieldCount++;
        var selectedType = $('#fieldTypeSelector').val();
        var newField = $(
            '<div class="field-container">' +
            '<label for="field' + fieldCount + '" class="editable-label">Campo ' + fieldCount + ':</label>' +
            '<input type="' + selectedType + '" name="field[]" id="field' + fieldCount + '" placeholder="Campo ' + fieldCount + '">' +
            '<button type="button" class="delete-button">Eliminar</button>' +
            '</div>'
        );
        $('#formFields').append(newField);
    });

    $('#dynamicForm').on('click', '.editable-label', function () {
        var $label = $(this);
        var currentText = $label.text();
        var $input = $('<input type="text" class="temp-input" value="' + currentText + '" />');

        $label.replaceWith($input);

        $input.focus();

        $input.blur(function () {
            var newText = $input.val();
            var $newLabel = $('<label for="' + $input.attr('for') + '" class="editable-label">' + newText + '</label>');
            $input.replaceWith($newLabel);
        });
    });

    $('#dynamicForm').on('click', '.delete-button', function () {
        $(this).closest('.field-container').remove();
    });

    $('#dynamicForm').submit(function (event) {
        event.preventDefault();
        var formData = $(this).serializeArray();
        console.log(formData); // Aquí puedes procesar los datos del formulario como desees
    });
});