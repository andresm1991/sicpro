let profile = document.querySelector('.profile');
let profileMenu = document.querySelector('.profile .menu');

let notificaciones = document.querySelector('.content-notificaciones');
let notificacionesMenu = document.querySelector('.content-notificaciones .menu');

if (profile && profileMenu) {
    profile.onclick = function (event) {
        notificacionesMenu.classList.remove('active');
        // Verifica si el clic fue en un elemento con la clase 'user' o 'img-box

        if (event.target.closest('.user') || event.target.closest('.img-box')) {
            profileMenu.classList.toggle('active');
        }
    };
}

if (notificaciones && notificacionesMenu) {
    notificaciones.onclick = function (event) {
        profileMenu.classList.remove('active');
        if (event.target.closest('.bell-icon')) {
            notificacionesMenu.classList.toggle('active');
        }
    };
}

// Evento global para ocultar los menús al hacer clic fuera de ellos
document.onclick = function (event) {
    // Oculta el menú de perfil si el clic fue fuera de él
    if (profile && profileMenu && !profile.contains(event.target) && !profileMenu.contains(event.target)) {
        profileMenu.classList.remove('active');
    }

    // Oculta el menú de notificaciones si el clic fue fuera de él
    if (notificaciones && notificacionesMenu && !notificaciones.contains(event.target) && !notificacionesMenu.contains(event.target)) {
        notificacionesMenu.classList.remove('active');
    }
};


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

// **Mensajes sweet Alerts **
function message() {
    return new Promise((resolve, reject) => {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se podrá revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, deseo continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(true); // Resuelve la promesa con "true" si se confirma
            } else {
                resolve(false); // Resuelve la promesa con "false" si se cancela
            }
        }).catch((error) => {
            reject(error); // Rechaza la promesa si hay un error
        });
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


/**
 * Calcular tiempo de entre fechas en formato H:m
 */
function calcularTiempoLaboral(fechaDesde, horaDesde, fechaHasta, horaHasta) {
    // Función auxiliar para convertir el formato de fecha 'd-m-Y' a 'Y-m-d'
    // que es más compatible con el constructor de Date.
    function parseDate(dmy) {
        let [day, month, year] = dmy.split('-');
        return `${year}-${month}-${day}`;
    }

    // Crear los objetos Date para el inicio y el fin.
    let inicio = new Date(`${parseDate(fechaDesde)}T${horaDesde}:00`);
    let fin = new Date(`${parseDate(fechaHasta)}T${horaHasta}:00`);

    // Validar que la fecha de fin sea posterior a la de inicio.
    // Y también que las fechas sean válidas (no NaN).
    if (fin <= inicio || isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
        return '00:00';
    }

    // Calcular la diferencia total en milisegundos.
    let diferenciaMs = fin.getTime() - inicio.getTime();

    // Convertir la diferencia de milisegundos a minutos totales.
    // Usamos Math.floor para no contar segundos parciales.
    let totalMinutos = Math.floor(diferenciaMs / (1000 * 60));

    // Calcular las horas y los minutos restantes.
    let horas = Math.floor(totalMinutos / 60);
    let minutos = totalMinutos % 60;

    // Formatear el resultado como "H:mm", asegurando que los minutos tengan dos dígitos.
    return `${horas}:${String(minutos).padStart(2, '0')}`;
}

$(function () {
    $('[data-toggle="popover"]').popover({ html: true });
    $('[data-toggle="tooltip"]').tooltip({ html: true });
    $('.dropdown-toggle').dropdown({
        container: 'body' // Forzar que el menú se renderice en el body
    });

    $("#show_hide_password a").on('click', function (event) {
        event.preventDefault();
        if ($('#show_hide_password input').attr("type") == "text") {
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass("fa-eye-slash");
            $('#show_hide_password i').removeClass("fa-eye");
        } else if ($('#show_hide_password input').attr("type") == "password") {
            $('#show_hide_password input').attr('type', 'text');
            $('#show_hide_password i').removeClass("fa-eye-slash");
            $('#show_hide_password i').addClass("fa-eye");
        }
    });

    $('#archivo').change(function (e) {
        let fileName = (e.target.files.length > 0) ? e.target.files[0].name : 'seleccionar archivo...';
        $('#archivo-label').text(fileName);
    });
    moment.locale('es');

    $('.daterange').daterangepicker({
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    }, function (start, end, label) {
        console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    });

    $('.daterange').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });
    $('.daterange').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });

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

    $('.timepicker').timepicker({
        timeFormat: 'HH:mm',
        interval: 30,
        minTime: '08',
        maxTime: '18:00',
        defaultTime: false,
        startTime: '08:00',
        dynamic: false,
        dropdown: true,
        scrollbar: true,
        useSelect: true,
        change: function (time) {
            const element = $(this);

            // Evita loops infinitos
            if (element.data('changing')) return;
            element.data('changing', true);

            let formattedTime = '';

            if (time instanceof Date) {
                const hours = String(time.getHours()).padStart(2, '0');
                const minutes = String(time.getMinutes()).padStart(2, '0');
                formattedTime = `${hours}:${minutes}`;
            } else {
                formattedTime = time;
            }

            // Establecer el nuevo valor y disparar el evento `change`
            element.val(formattedTime);
            element.trigger('change');

            element.data('changing', false); // Reset
        }
    });

    $('.timepickerSinHorario').timepicker({
        timeFormat: 'HH:mm',
        interval: 30,
        defaultTime: false,
        startTime: '06:00',
        dynamic: false,
        dropdown: true,
        scrollbar: true,
        useSelect: true,
        change: function (time) {
            const element = $(this);

            // Evita loops infinitos
            if (element.data('changing')) return;
            element.data('changing', true);

            let formattedTime = '';

            if (time instanceof Date) {
                const hours = String(time.getHours()).padStart(2, '0');
                const minutes = String(time.getMinutes()).padStart(2, '0');
                formattedTime = `${hours}:${minutes}`;
            } else {
                formattedTime = time;
            }

            // Establecer el nuevo valor y disparar el evento `change`
            element.val(formattedTime);
            element.trigger('change');

            element.data('changing', false); // Reset
        }
    });

    $("select").select2({
        with: 'resolve',
    });

    $('.select2-basic-multiple').each(function () {
        let $select = $(this);

        let config = {
            width: '100%',
            placeholder: $select.data('placeholder'), // Get the placeholder from data-placeholder
            allowClear: false,
        };

        // Guardar la configuración original en `data()`
        $select.data('select2-config', config).select2(config);
    });

    $('.select2-basic-single').each(function () {
        let $select = $(this);

        let config = {
            width: '100%',
            placeholder: function () {
                $(this).data('placeholder');
            },
            allowClear: false,
        };

        // Guardar la configuración original en `data()`
        $select.data('select2-config', config).select2(config);
    });

    $('.select2-multiple').select2({
        width: '100%',
        //minimumResultsForSearch: Infinity,
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

    $('.select2-tag').each(function () {
        let $select = $(this);

        let config = {
            width: '100%',
            allowClear: false, // Permite limpiar la selección
            tags: true, // Permite agregar nuevas opciones escribiendo
            placeholder: function () {
                return $(this).data('placeholder');
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            },
            insertTag: function (data, tag) {
                data.unshift(tag); // Inserta la nueva opción al principio
            }
        };

        // Guardar la configuración original en `data()`
        $select.data('select2-config', config).select2(config);
    });

    // Input mask
    $('.input-double').inputmask({
        alias: 'decimal',  // Usar el alias "decimal"
        radixPoint: ".",   // Definir el punto decimal
        groupSeparator: "",  // Separador de miles (opcional)
        digits: 4,         // Número de dígitos decimales
        autoGroup: true,   // Agrupar los miles
        rightAlign: false, // Alinear a la izquierda
        allowMinus: false,   // Permitir números negativos
        placeholder: $('.input-double').data('placeholder')
    });

    $('.simple-double').inputmask({
        alias: 'decimal',  // Usar el alias "decimal"
        digits: 100,
        digitsOptional: true,
        rightAlign: false,
        allowMinus: true,
        placeholder: $('.simple-double').data('placeholder'),
    });

    $('.input-enteros').inputmask({
        alias: 'numeric',
        rightAlign: false,      // No alinear a la derecha
        allowMinus: false,      // No permitir números negativos
        digits: 0,              // No permitir decimales
        min: 0,                 // Valor mínimo (0 o positivo)
        placeholder: $('.input-enteros').data('placeholder'),         // Dejar vacío el placeholder si lo deseas
        autoUnmask: true         // Para que el valor sea guardado sin el formato de máscara
    });

    Inputmask({
        alias: "currency",
        prefix: "$ ",                // Símbolo de dólar
        groupSeparator: "",         // Separador de miles
        autoGroup: true,             // Agrupación automática
        digits: 4,                   // Número de decimales
        digitsOptional: false,       // Asegura siempre dos decimales
        placeholder: "0",            // Marcador de posición
        clearMaskOnLostFocus: true,   // Limpia la máscara si está vacío
        unmaskAsNumber: true          // Convierte el valor en número sin el símbolo
    }).mask(".currency");

    Inputmask({
        alias: "currency",
        prefix: "$ ",                // Símbolo de dólar
        groupSeparator: ",",         // Separador de miles
        autoGroup: true,             // Agrupación automática
        digits: 4,                   // Número de decimales
        digitsOptional: false,       // Asegura siempre dos decimales
        placeholder: "0",            // Marcador de posición
        clearMaskOnLostFocus: true,   // Limpia la máscara si está vacío
        unmaskAsNumber: true          // Convierte el valor en número sin el símbolo
    }).mask(".currency_separador_miles");

    Inputmask({
        alias: "currency",
        prefix: "$ ",                // Símbolo de dólar
        groupSeparator: "",         // Separador de miles
        autoGroup: true,             // Agrupación automática
        digits: 2,                   // Número de decimales
        digitsOptional: true,       // Asegura siempre dos decimales
        placeholder: "0",            // Marcador de posición
        clearMaskOnLostFocus: true,   // Limpia la máscara si está vacío
        unmaskAsNumber: true,          // Convierte el valor en número sin el símbolo
    }).mask(".currency_two_decimals");

    Inputmask({
        alias: "datetime",
        inputFormat: "HH:MM",
        placeholder: "00:00",

    }).mask(".dateTime");

    $('.money').maskMoney({ prefix: '$ ', allowNegative: true, affixesStay: false, precision: 4 });
    $('.moneyDosDecimales').maskMoney({ prefix: '$ ', allowNegative: true, affixesStay: false, precision: 2 });

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

    /*=========== Files Input style Dropify ========*/
    var drEvent = $('.dropify').dropify({
        tpl: {
            wrap: '<div class="dropify-wrapper"></div>',
            loader: '<div class="dropify-loader"></div>',
            message: '<div class="dropify-message"><span class="file-icon" /> <p>{{ default }}</p></div>',
            preview: '<div class="dropify-preview"><span class="dropify-render"></span><div class="dropify-infos"><div class="dropify-infos-inner"><p class="dropify-infos-message">{{ replace }}</p></div></div></div>',
            filename: '',
            clearButton: '<button type="button" class="dropify-clear">{{ remove }}</button>',
            errorLine: '<p class="dropify-error">{{ error }}</p>',
            errorsContainer: '<div class="dropify-errors-container"><ul></ul></div>'
        },
        messages: {
            default: 'arrastre y suelte un archivo aquí o haga click',
            replace: 'arrastrar y soltar o hacer clic para reemplazar',
            remove: 'BORRAR',
            error: 'Lo sentimos, el archivo es demasiado grande',
        }
    });

    //** Permite el ingreso del numero con el formato 1234-1//
    $('#nro_factura').on('input', function () {
        let value = $(this).val();

        // Permitir solo números y un único "-" después de un número
        value = value.replace(/[^0-9-]/g, ''); // Eliminar caracteres no válidos

        // Permitir un único "-" después de un número
        if (!/^\d+-?\d*$/.test(value)) {
            value = value.replace(/-/, ''); // Eliminar guiones en posiciones inválidas
        }

        // Eliminar guiones adicionales si ya existe uno
        value = value.replace(/(?!^)-(?=.*-)/g, '');

        // Actualizar el valor del input
        $(this).val(value);
    });

    $(document).on('change', '.inventario', function () {
        if ($(this).is(':checked')) {
            $(this).val(1);
        } else {
            $(this).val(0);
        }
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

    /**
     * Calculpo entre valor unitario y cantidad
     */
    $(document).on('input', '#table-adquisiciones', '.cantidad input, .iva-producto, .precio-unitario', function () {
        calcularTotalFilasYGeneral();
    });


    $('.precio-unitario').on('blur', function () {
        var valor_actual = $(this).data('valor-actual') != undefined ? parseFloat(String($(this).data('valor-actual')).replace(/[^0-9.]/g, '')) : 0;
        var valor_ingresado = parseFloat($(this).val().replace(/[^0-9.]/g, '')) || 0;
        console.log(valor_actual);
        console.log(valor_ingresado)
        if (valor_actual != 0) {
            if (valor_ingresado > valor_actual) {
                var diferencia = valor_ingresado - valor_actual;
                Swal.fire(
                    'Aviso!',
                    `El valor ingresado $ ${valor_ingresado.toFixed(4)} es mayor al valor actual ${valor_actual.toFixed(4)} por una diferencia de $ ${diferencia.toFixed(4)}`,
                    'info'
                );
            } else if (valor_ingresado < valor_actual) {
                var diferencia = valor_actual - valor_ingresado;
                Swal.fire(
                    'Aviso!',
                    `El valor ingresado $ ${valor_ingresado.toFixed(4)} es menor al valor actual ${valor_actual.toFixed(4)} por una diferencia de $ ${diferencia.toFixed(4)}`,
                    'info'
                );
            }
        }
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

    $('.leer-notificacion').on('click', function () {
        var id = $(this).data('id');
        var url = $(this).data('url');
        // Redirigir a la URL de la notificación
        $.ajax({
            url: base_url + "/notificaciones/leer-notificacion",
            type: "POST",
            data: {
                id: id,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    // Abrir la URL en otra pestaña
                    if (url != '/') {
                        window.open(url,
                            '_blank'); // '_blank' abre la URL en una nueva pestaña
                    }
                    // Actualizar el contador de notificaciones
                    var cantidad = parseInt($('.notification-amount').text());
                    if (cantidad > 0) {
                        var cantidad = parseInt($('.notification-amount').text()) - 1;
                        $('.notification-amount').text(cantidad);
                    }

                    // Cambiar el color de la notificación a gris
                    $('.leer-notificacion[data-id="' + id + '"]').removeClass('list-group-item-dark');
                }
            }
        });
    });

    $('.content-notificaciones').on('click', function () {
        $.ajax({
            url: base_url + "/notificaciones/marcar-todos-leido",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('.notification-amount').text('0');
            }
        });
    });

});

function calcularTotalProducto($cantidad, $valor, $iva) {
    console.log($cantidad);
    console.log($valor);
    console.log($iva);
    let totalFila = $cantidad * $valor;
    let iva = (totalFila * $iva) / 100;
    let total = totalFila + iva;
    return total;
}

// Función para calcular el total de cada fila y el total general
function calcularTotalFilasYGeneral() {
    let totalGeneral = 0;

    $('.precio-unitario').each(function () {
        let index = $(this).data('index');
        let cantidad;

        // Verifica si 'cantidad' es un campo input o un texto en td
        if ($(`.cantidad[data-index='${index}'] input`).length > 0) {
            cantidad = parseFloat($(`.cantidad[data-index='${index}'] input`).val()) || 0;
        } else {
            cantidad = parseFloat($(`.cantidad[data-index='${index}']`).text()) || 0;
        }

        let precioUnitario = parseFloat($(this).val().replace(/[^0-9.]/g, '')) || 0;
        let totalFila = cantidad * precioUnitario;
        let porcentaje_iva = parseFloat($(`.iva-producto[data-index='${index}']`).val()) || 0;
        let iva = (totalFila * porcentaje_iva) / 100;
        let total = totalFila + iva;
        // Actualiza el total de la fila en el td.calculo-total correspondiente
        $(`.calculo-total[data-index='${index}']`).text(`$ ${total.toFixed(4)}`);

        // Sumar el total de esta fila al total general
        totalGeneral += total;
    });

    // Actualiza el total general
    $('#total-general').text(`$ ${totalGeneral.toFixed(4)}`);
}


// Calcular el total de cada fila
function calcularTotal($element) {
    let subtotal = 0;

    // Iterar por cada fila del tbody
    $($element).each(function () {
        let totalText = $(this).find('.total_unitario').text().replace(/[^0-9.,]/g, ''); // Extraer números y coma/decimal
        let total = parseFloat(totalText.replace(',', '.')) || 0; // Reemplazar la coma decimal por un punto y convertir a número
        // Sumar al subtotal
        subtotal += total;
    });
    // Actualizar el total general
    return subtotal.toFixed(4);
}

function inicializarPlugins() {
    // Input mask
    $('.input-double').inputmask({
        alias: 'decimal',  // Usar el alias "decimal"
        radixPoint: ",",   // Definir el punto decimal
        groupSeparator: ".",  // Separador de miles (opcional)
        digits: 4,         // Número de dígitos decimales
        autoGroup: true,   // Agrupar los miles
        rightAlign: false, // Alinear a la izquierda
        allowMinus: false,   // Permitir números negativos
        placeholder: $('.input-double').data('placeholder')
    });

    $('.simple-double').inputmask({
        alias: 'decimal',  // Usar el alias "decimal"
        radixPoint: ",",   // Definir el punto decimal
        groupSeparator: ".",  // Separador de miles (opcional)
        autoGroup: false,   // Agrupar los miles
        rightAlign: false, // Alinear a la izquierda
        allowMinus: true,   // Permitir números negativos
        placeholder: $('.simple-double').data('placeholder')
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

    Inputmask({
        alias: "currency",
        prefix: "$ ",                // Símbolo de dólar
        groupSeparator: "",         // Separador de miles
        autoGroup: true,             // Agrupación automática
        digits: 4,                   // Número de decimales
        digitsOptional: false,       // Asegura siempre dos decimales
        placeholder: "0",            // Marcador de posición
        clearMaskOnLostFocus: true,   // Limpia la máscara si está vacío
        unmaskAsNumber: true          // Convierte el valor en número sin el símbolo
    }).mask(".currency");

    Inputmask({
        alias: "currency",
        prefix: "$ ",                // Símbolo de dólar
        groupSeparator: ",",         // Separador de miles
        autoGroup: true,             // Agrupación automática
        digits: 4,                   // Número de decimales
        digitsOptional: false,       // Asegura siempre dos decimales
        placeholder: "0",            // Marcador de posición
        clearMaskOnLostFocus: true,   // Limpia la máscara si está vacío
        unmaskAsNumber: true          // Convierte el valor en número sin el símbolo
    }).mask(".currency_separador_miles");

    Inputmask({
        alias: "currency",
        prefix: "$ ",                // Símbolo de dólar
        groupSeparator: "",         // Separador de miles
        autoGroup: true,             // Agrupación automática
        digits: 2,                   // Número de decimales
        digitsOptional: true,       // Asegura siempre dos decimales
        placeholder: "0",            // Marcador de posición
        clearMaskOnLostFocus: true,   // Limpia la máscara si está vacío
        unmaskAsNumber: true,          // Convierte el valor en número sin el símbolo
    }).mask(".currency_two_decimals");

    Inputmask({
        alias: "datetime",
        inputFormat: "HH:MM",
        placeholder: "00:00",

    }).mask(".dateTime");

    $('.money').maskMoney({ prefix: '$ ', allowNegative: true, affixesStay: false });

    $('.select2-tag').each(function () {
        let $select = $(this);

        let config = {
            width: '100%',
            allowClear: false, // Permite limpiar la selección
            tags: true, // Permite agregar nuevas opciones escribiendo
            placeholder: function () {
                return $(this).data('placeholder');
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            },
            insertTag: function (data, tag) {
                data.unshift(tag); // Inserta la nueva opción al principio
            }
        };

        // Guardar la configuración original en `data()`
        $select.data('select2-config', config).select2(config);
    });

    $("select").select2({
        with: 'resolve',
    });
}

function formatearUSD(valor) {
    return Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 4,
        maximumFractionDigits: 4
    }).format(valor)
}


/**
 * Validar campos de un formulario
 * @param {Array} campos - Array de objetos con los selectores y mensajes de error
 * @returns {boolean} - Retorna true si todos los campos son válidos, false si no
 */
function validarCampos(campos) {
    let isValid = true;

    // Limpiar errores previos
    $('.error-border').removeClass('error-border');
    $('.error-message').remove();

    campos.forEach(campo => {
        const elemento = $(campo.selector);
        const valor = elemento.val();

        if (campo.selector != '#iva' && (valor === null || valor.trim() === '' || valor === '0')) {
            isValid = false;
            // Agregar borde rojo y mensaje de error
            if (elemento.prop('tagName').toLowerCase() === 'select') {
                elemento.next('.select2-container').find('.select2-selection').addClass('error-border');
            } else {
                elemento.addClass('error-border');
            }

            elemento.parent().append(`<span class="error-message">${campo.mensaje}</span>`);
        }
    });

    return isValid;
}

/**
 * Limpiar los campos de un formulario
 * @param {Array} campos - Array de selectores de los campos a limpiar
 */
function limpiarCampos(campos) {
    campos.forEach(campo => {
        const elemento = $(campo.selector);
        if (elemento.prop('tagName').toLowerCase() === 'select') {
            elemento.val(null).trigger('change');
        } else {
            elemento.val('');
        }
    });
}

// Función para formatear el precio (ejemplo: "1,500.50" → 1500.50)
function parsePrecio(texto) {
    return parseFloat(texto.replace(/[^\d.-]/g, '')) || 0;
}



//** Función para obtener la ubicación*/
function getLocation() {
    return new Promise(function (resolve, reject) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    // Resuelve la promesa con las coordenadas
                    resolve({
                        latitud: position.coords.latitude.toFixed(8),
                        longitud: position.coords.longitude.toFixed(8)
                    });
                },
                function (error) {
                    // Rechaza la promesa con el error
                    reject(error);
                }
            );
        } else {
            reject("La geolocalización no es compatible con este navegador.");
        }
    });
}

// Función para manejar errores
function showError(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            alert("El usuario denegó la solicitud de geolocalización.");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("La información de ubicación no está disponible.");
            break;
        case error.TIMEOUT:
            alert("La solicitud para obtener la ubicación del usuario excedió el tiempo de espera.");
            break;
        case error.UNKNOWN_ERROR:
            alert("Ocurrió un error desconocido.");
            break;
    }
}