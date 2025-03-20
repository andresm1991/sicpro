import { getFormData } from './helpers.js';

$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    $('.generar-reporte').on('click', function () {
        const action = $(this).data('action'); // Obtener el valor de data-action

        // Actualizar el campo oculto con el tipo de reporte
        $('#tipo_reporte').val(action);

        // Obtener la acción base del formulario (sin modificaciones previas)
        let formAction = $('#form-reporte-adquisiciones').attr('action');

        // Eliminar cualquier sufijo previo agregado al action
        formAction = formAction.split('/pdf')[0].split('/excel')[0];

        // Agregar el valor de tipo_reporte como parámetro en la URL
        $('#form-reporte-adquisiciones').attr('action', `${formAction}/${action}`);

        // Enviar el formulario
        $('#form-reporte-adquisiciones').submit();
    });
});