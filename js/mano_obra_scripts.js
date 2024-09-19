$(function () {
    // Evento al hacer clic en el botón para clonar
    $('.btn-clonar').on('click', function() {
        // Clonar la fila con clase 'fila-clonable' y mantener eventos y datos asociados
        var $filaOriginal = $('.fila-clonable').first();  // Obtener la fila original
        var $filaClonada = $filaOriginal.clone();  // Clonamos la fila
        
        // Limpiar los valores de los campos de input y select en la fila clonada
        $filaClonada.find('input').val('');  // Limpiar todos los inputs
        
        // Limpiar los selects y evitar destruir select2 en la fila original
        $filaClonada.find('select').each(function() {
            $(this).val(null).trigger('change.select2');  // Limpia la selección del select en la fila clonada
        });
        
        // Añadir la fila clonada al final del tbody de la tabla
        $('#tabla-planificacion tbody').append($filaClonada);

        // Inicializar Select2 en los selects de la nueva fila clonada
        $filaClonada.find('select').each(function() {
            $(this).select2();  // Inicializar select2 para los select de la fila clonada
        });
         
    });
});