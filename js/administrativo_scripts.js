$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    /**
     * Filtrar adquisiciones
     */

    $('input:text[name=adquisicion_search]').on('keyup', function () {
        var $value = $(this).val();

        $.ajax({
            url: 'buscar-adquisicion',
            headers: { 'X-CSRF-TOKEN': csrf },
            type: 'GET',
            data: { 'buscar': $value },
            beforeSend: function () {
            },
            success: function (data) {
                $('tbody').html(data);
                $('[data-toggle="tooltip"]').tooltip();
                $('[data-toggle="popover"]').popover({ html: true });
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var errors = JSON.parse(jqXHR.responseText);
            console.log(errors)
        });
    });
});