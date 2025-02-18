$(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');

    if ($('#tabla-adquisiciones').length) {
        $('#tabla-adquisiciones').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url + "/data-adquisiciones",
                headers: { 'X-CSRF-TOKEN': csrf },
                type: 'GET',
            },
            columns: [
                { data: 'id', name: 'id' }, // Columna #
                { data: 'fecha', name: 'fecha' }, // Columna Fecha Pedido
                { data: 'proyecto', name: 'proyecto' }, // Columna Proyecto
                { data: 'etapa', name: 'etapa' }, // Columna Etapa
                { data: 'tipo_etapa', name: 'tipo_etapa' }, // Columna Tipo
                { data: 'estado', name: 'estado' }, // Columna Estado
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false } // Columna Acciones
            ],
            pageLength: 10, // Número de registros por página
            language: {
                url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json" // Traducción al español
            }
        });
    }
});