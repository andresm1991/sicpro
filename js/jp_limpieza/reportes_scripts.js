import { getFormData } from '../helpers.js';
$(function () {

    var csrf = $('meta[name="csrf-token"]').attr('content');

    $('#generar-reporte-adquisicion').click(function () {
        let data = getFormData($('#form-reporte'));
        console.log(data);
        $.ajax({
            url: 'visualizar-reporte-adquisiciones',
            type: 'POST',
            data: data,
            success: function (response) {
                console.log(response);
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });
});