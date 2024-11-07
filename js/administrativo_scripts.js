$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    // Obtén la URL completa
    let url = window.location.href;

    // Divide la URL por cada "/"
    let segments = url.split('/');

    // Obtén el último segmento
    let lastSegment = segments.pop() || segments.pop();  // Maneja caso de '/' al final

    /**
     * Filtrar adquisiciones
     */

    $('input:text[name=adquisicion_search]').on('keyup', function () {
        var $value = $(this).val();

        $.ajax({
            url: url+'/buscar-adquisicion',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value },
            beforeSend: function () {
            },
            success: function (data) {
                $('tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX:", error);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });
});