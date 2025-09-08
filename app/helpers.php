<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Tarea;
use App\Models\Cliente;
use App\Models\Articulo;
use Carbon\CarbonPeriod;
use App\Models\Proveedor;
use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use App\Models\OrdenRecepcion;
use App\Models\DiccionarioPalabra;
use App\Models\JPLimpieza\Producto;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Models\DetalleResumenPagoSemanal;
use App\Models\JPLimpieza\DetalleAdquisicion;
use App\Models\JPLimpieza\CategoriaPresupuesto;
use App\Models\JPLimpieza\PlantillaPresupuesto;
use App\Models\ProformaProducto;

if (!function_exists('encrypted_route')) {
    function encrypted_route($name, $parameters = [], $absolute = true)
    {
        foreach ($parameters as $key => $value) {
            if ($value) {
                $parameters[$key] = Crypt::encrypt($value);
            }
        }

        return route($name, $parameters, $absolute);
    }
}

if (!function_exists('explode_param')) {
    function explode_param($param, $index = 0)
    {
        $delimiters = "/[,;|\-]/";
        $array = preg_split($delimiters, $param);

        if (count($array) < 3) {
            array_push($array, '');
        }

        return $array;
    }
}
if (!function_exists('doTemporaryUrl')) {
    function doTemporaryUrl($fileName)
    {
        if ($fileName) {
            // Define la duración de validez de la URL firmada
            $expiresAt = Carbon::now()->addMinutes(5);

            // Genera la URL firmada
            $url = Storage::disk('digitalocean')->temporaryUrl($fileName, $expiresAt);

            return $url;
        }
        // Devuelve una URL por defecto o una cadena vacía si no hay imagen.
        return 'https://upload.wikimedia.org/wikipedia/commons/0/0a/No-image-available.png';
    }
}
if (!function_exists('dateFormat')) {
    function dateFormat($format_origin, $format_result, $fecha)
    {
        if (isset($fecha)) {
            return Carbon::createFromFormat($format_origin, $fecha)->format($format_result);
        }
    }

    function dateFormatHumans($date)
    {
        Carbon::setLocale('es');
        // Obtener y formatear la fecha
        $fecha = Carbon::parse($date);
        $fechaFormateada = $fecha->isoFormat('dddd D [de] MMMM [de] YYYY');
        // Convertir a título solo las palabras necesarias
        $fechaFormateada = preg_replace_callback('/\b(?:[^\s]*)\b/u', function ($matches) {
            $palabra = $matches[0];
            // Mantener 'de' en minúsculas
            return in_array(strtolower($palabra), ['de']) ? $palabra : ucfirst($palabra);
        }, $fechaFormateada);

        return $fechaFormateada;
    }

    function dateFormatHumansManoObra($date_start, $date_end)
    {
        Carbon::setLocale('es');
        // Convertir las fechas de cadena a objetos Carbon
        $fechaInicio = Carbon::createFromFormat('Y-m-d', $date_start);
        $fechaFin = Carbon::createFromFormat('Y-m-d',  is_null($date_end) ? date('Y-m-d') : $date_end);

        // Formatear las fechas al estilo solicitado
        $formato = 'D [de] MMMM';
        $fechaFormateada = 'Del ' . $fechaInicio->isoFormat($formato) . ' al ' . $fechaFin->isoFormat($formato . ' YYYY');
        $fechaFormateada = preg_replace_callback('/\b(?:[^\s]*)\b/u', function ($matches) {
            $palabra = $matches[0];
            // Mantener 'de' en minúsculas
            return in_array(strtolower($palabra), ['de', 'al']) ? $palabra : ucfirst($palabra);
        }, $fechaFormateada);
        return $fechaFormateada;
    }


    function dateFormatHumansToDate($date)
    {
        Carbon::setLocale('es');
        $fechas = [];
        // Extraer las fechas con una expresión regular
        preg_match_all('/(\d{1,2} de \w+ de \d{4})/', $date, $matches);

        if (isset($matches[1]) && count($matches[1]) === 2) {
            // Convertir las fechas con Carbon
            $fecha_inicio = Carbon::createFromFormat('j \d\e F \d\e Y', $matches[1][0])->format('Y-m-d');
            $fecha_fin = Carbon::createFromFormat('j \d\e F \d\e Y', $matches[1][1])->format('Y-m-d');

            $fechas = ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin];
        }

        return $fechas;
    }

    // Función para validar y formatear la fecha
    function validarYFormatearFecha($fecha)
    {
        // Intentar crear un objeto Carbon desde el formato 'd-m-Y'
        try {
            return Carbon::createFromFormat('d-m-Y', $fecha)->format('Y-m-d');
        } catch (\Exception $e) {
            // Si falla, asumimos que la fecha ya está en formato 'Y-m-d'
            return $fecha;
        }
    }

    function diferencia_horas($fechaInicio, $fechaFin)
    {
        $segundos = Carbon::parse($fechaInicio)->diffInSeconds(Carbon::parse($fechaFin));

        return gmdate('H:i:s', $segundos);
    }

    function semanasTranscurridas($fechaInicio, $diaFinSemana = Carbon::SATURDAY)
    {
        $inicio = Carbon::parse($fechaInicio);
        $hoy = Carbon::now();

        // Ajustar ambas fechas al ciclo semanal deseado
        $inicioAjustado = $inicio->copy()->previous($diaFinSemana);
        $hoyAjustado = $hoy->copy()->previous($diaFinSemana);

        return $inicioAjustado->diffInWeeks($hoyAjustado);
    }

    function semanaEnCurso($fechaIncio)
    {
        // Fechas de inicio y fin del rango
        $start = Carbon::parse($fechaIncio)->startOfWeek(); // ajustamos al lunes
        $end = Carbon::parse(now())->endOfWeek();     // ajustamos al domingo

        // Creamos un periodo de semanas completas
        $weeks = CarbonPeriod::create($start, '1 week', $end);

        // Fecha actual
        $today = Carbon::now();

        // Variables de seguimiento
        $weekNumber = 0;
        $totalWeeks = 0;

        foreach ($weeks as $weekStart) {
            $totalWeeks++;
            $weekEnd = (clone $weekStart)->endOfWeek();

            if ($today->between($weekStart, $weekEnd)) {
                $weekNumber = $totalWeeks;
            }
        }

        return $weekNumber;
    }

    function obtenerSemanasEntreFechas($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfWeek(); // lunes
        $end = Carbon::parse($endDate)->endOfWeek();       // domingo

        $period = CarbonPeriod::create($start, '1 week', $end);

        return iterator_count($period);
    }

    /**
     * Calcular la diferencia entre las fechas y horas en formato H:m.
     */
    function calcularTiempoTotal($fechaDesde, $horaDesde, $fechaHasta, $horaHasta)
    {
        // Validar y formatear las fechas
        $fechaDesdeFormatted = validarYFormatearFecha($fechaDesde);
        $fechaHastaFormatted = validarYFormatearFecha($fechaHasta);

        $horaDesde = substr($horaDesde, 0, 5);
        $horaHasta = substr($horaHasta, 0, 5);

        // Crear objetos Carbon para el inicio y el fin
        // Crea objetos Carbon (sin conversión automática a UTC)
        $inicio = Carbon::createFromFormat('Y-m-d H:i', "$fechaDesdeFormatted $horaDesde", 'America/Lima');
        $fin = Carbon::createFromFormat('Y-m-d H:i', "$fechaHastaFormatted $horaHasta", 'America/Lima');

        // Definir el horario laboral
        $horaInicioLaboral = '05:00';
        $horaFinLaboral = '18:00';

        // Inicializar el total de minutos laborales
        $totalMinutosLaborales = 0;

        // Iterar sobre cada día en el rango de fechas
        $periodo = CarbonPeriod::create($inicio, $fin);
        foreach ($periodo as $dia) {
            // Saltar los fines de semana (opcional)
            if ($dia->isWeekend()) {
                continue;
            }

            // Definir el inicio y fin del día laboral
            $inicioDiaLaboral = Carbon::parse($dia->format('Y-m-d') . ' ' . $horaInicioLaboral);
            $finDiaLaboral = Carbon::parse($dia->format('Y-m-d') . ' ' . $horaFinLaboral);

            // Determinar el rango efectivo para este día
            $inicioEfectivo = $inicio->greaterThan($inicioDiaLaboral) ? $inicio : $inicioDiaLaboral;
            $finEfectivo = $fin->lessThan($finDiaLaboral) ? $fin : $finDiaLaboral;

            // Asegurarse de que el rango efectivo esté dentro del horario laboral
            if ($inicioEfectivo->lessThanOrEqualTo($finEfectivo)) {
                $totalMinutosLaborales += $inicioEfectivo->diffInMinutes($finEfectivo);
            }
        }

        // Convertir el total de minutos a horas y minutos
        $horas = intdiv($totalMinutosLaborales, 60); // Horas completas
        $minutos = $totalMinutosLaborales % 60; // Minutos restantes

        // Formatear el resultado como "H:m"
        return sprintf('%d:%02d', $horas, $minutos);
    }

    /**
     * Calcular los días entre dos fechas.
     */
    function diasEntreFechas($date1, $date2)
    {
        // Create DateTime objects from the date strings
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);

        // Calculate the difference
        $interval = $datetime1->diff($datetime2);

        // Get the number of days
        return $interval->days;
    }
}

if (!function_exists('generateProductCode')) {
    function generateProductCode($type)
    {
        // Define the prefix based on the type
        $prefix = $type === 'bien' ? 'B-' : 'S-';

        // Define the length of the random part of the code
        $randomLength = 8; // Change this value as needed

        // Generate a random alphanumeric string
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < $randomLength; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Combine the prefix and the random string to form the product code
        $productCode = $prefix . $randomString;

        return $productCode;
    }
}

if (!function_exists('registrarProducto')) {
    function registrarProducto($tipo, $descripcion, $valor = null, $iva = null)
    {
        $tipo_producto = $tipo->slug == 'meteriales.herramientas' ? 'tipo.adquisiciones.bienes' : 'tipo.adquisiciones.servicios';
        $categoria = CatalogoDato::where('slug', $tipo_producto)->first();
        $type = $tipo->slug == 'meteriales.herramientas' ? 'B-' : 'S-';
        $code = generateProductCode($type);
        $create =  Articulo::create(['categoria_id' => $categoria->id, 'codigo' => $code, 'descripcion' => $descripcion, 'valor_unitario' => $valor, 'iva' => $iva, 'activo' => true]);

        return $create;
    }

    /**
     * Registrar unidad de medida
     * @param unidad_medida
     * @return ultimo_id
     */
    function registrarUnidadMedida($unidad_medida)
    {
        $slug = strtolower(str_replace(' ', '.', $unidad_medida));
        $catalogo = CatalogoDato::getCatalogoPadre('unidades.medida');
        $existe = CatalogoDato::whereRaw('LOWER(descripcion) = ?', [strtolower($unidad_medida)])
            ->whereRaw('LOWER(slug) = ?', [strtolower('unidad.medida.' . $slug)])
            ->first();

        if (!$existe) {
            $create = CatalogoDato::create([
                'descripcion' => $unidad_medida,
                'detalle' => '',
                'slug' => 'unidad.medida.' . $slug,
                'padre_id' => $catalogo->id,
                'activo' => true,
            ]);
            return $create->id;
        }
        return $existe->id;
    }

    /**
     * Registrar nuevo elemento en Catalogo datos
     * @param String actividad
     * @return int id
     */
    function newChildrenCatalogoDatos($value, $slug_padre)
    {
        $slug = strtolower(str_replace(' ', '.', $value));
        $catalogo = CatalogoDato::getCatalogoPadre($slug_padre);
        $existe = CatalogoDato::where('descripcion', $value)
            ->where('slug', $slug)->first();

        if (!$existe) {
            $create = CatalogoDato::create([
                'descripcion' => $value,
                'detalle' => '',
                'slug' => $catalogo->slug . '.' . $slug,
                'padre_id' => $catalogo->id,
                'activo' => true,
            ]);
            return $create->id;
        }
        return $existe->id;
    }

    function formasPagos()
    {
        $forma_pagos = CatalogoDato::getChildrenCatalogo('formas.pagos')->pluck('descripcion', 'id');
        return $forma_pagos;
    }

    function getTipoPagos($isSelected = false)
    {
        $tipo_pagos = CatalogoDato::getChildrenCatalogo('metodos.pagos')->pluck('descripcion', 'id');
        if ($isSelected) {
            $tipo_pagos->prepend('', '');
        }
        return $tipo_pagos;
    }

    function getEstadosPagos($isSelected = false)
    {
        $estados_pagos = CatalogoDato::getChildrenCatalogo('estados.pagos.prestamos')->pluck('descripcion', 'id');
        if ($isSelected) {
            $estados_pagos->prepend('', '');
        }
        return $estados_pagos;
    }

    function getUnidadMedidas($isSelected = false)
    {
        $unidad_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida');
        if ($isSelected) {
            $unidad_medidas = $unidad_medidas->pluck('descripcion', 'id')->prepend('', '');
        } else {
            $unidad_medidas = $unidad_medidas->get();
        }
        return $unidad_medidas;
    }

    function getPorcentajeIva($tipo, $isSelectOptions = false)
    {
        $iva_productos = CatalogoDato::getChildrenCatalogo($tipo == 'productos' ? 'iva.productos' : 'iva.general');
        if ($isSelectOptions) {
            $iva_productos = $iva_productos->pluck('descripcion', 'id')->prepend('', '');
        }

        return $iva_productos;
    }

    function calcularTotalProducto($cantidad, $valor, $iva, $costo_indirecto = 0)
    {
        $valor = calcularProcentaje($valor, $costo_indirecto, 4);
        $subTotal = $cantidad * $valor;
        $iva = $iva ?? 0;
        $iva = ($subTotal * $iva) / 100;
        $total = $subTotal + $iva;
        return  $total;
    }


    /**
     * La tasa de interés semanal se calcula dividiendo la tasa anual por 52 semanas.
     */
    function calcularCuotaSemanalPrestamo($monto, $interes, $plazoEnSemanas)
    {
        $tasaSemanal = ($interes / 100) / 52; // Tasa semanal
        if ($tasaSemanal == 0) {
            return $monto / $plazoEnSemanas; // Sin interés
        }

        $cuota = $monto * $tasaSemanal / (1 - pow(1 + $tasaSemanal, -$plazoEnSemanas));
        return round($cuota, 2);
    }

    /** Función para calcular las fechas de pago que sean los viernes de cada semana segun el plazo */
    function calcularFechasPago($fechaInicio, $plazoSemanas)
    {
        $fechaInicio = Carbon::parse($fechaInicio);
        $fechasPago = [];

        // Si la fecha inicial ya es un viernes, comenzar desde el siguiente viernes
        if ($fechaInicio->dayOfWeek === Carbon::FRIDAY) {
            $fechaInicio->addWeek();
        } else {
            // Asegurar que la fecha inicial sea el próximo viernes
            $fechaInicio = $fechaInicio->next(Carbon::FRIDAY);
        }

        for ($i = 0; $i < $plazoSemanas; $i++) {
            $fechasPago[] = $fechaInicio->copy();
            $fechaInicio->addWeek();
        }

        return $fechasPago;
    }
}
/**
 * Formato para el numero de orden de trabajo o adquisison
 * @param Model
 * return $numero_orden
 */
if (!function_exists('numeroOrden')) {
    function numeroOrden($numero, $nuevo = true)
    {
        if ($nuevo) {
            $ultimo_id = $numero ? $numero->id + 1 : 1;
            $numero_orden = date('Ymd') . '-' . str_pad($ultimo_id, 3, '0', STR_PAD_LEFT);
        } else {
            if ($numero) {
                $ultimo_id = $numero->id;
            } else {
                $numero = OrdenRecepcion::latest()->first();
                $ultimo_id = $numero ? $numero->id + 1 : 1;
            }

            $numero_orden = date('Ymd', strtotime($numero->fecha)) . '-' . str_pad($ultimo_id, 3, '0', STR_PAD_LEFT);
        }

        return $numero_orden;
    }

    function generarNumeroOrden()
    {
        $ultimo_registro = Adquisicion::latest()->first();
        $ultimo_id = $ultimo_registro ? $ultimo_registro->id + 1 : 1;
        $numero_orden = date('Ymd') . '-' . str_pad($ultimo_id, 3, '0', STR_PAD_LEFT);

        return $numero_orden;
    }

    function numeroPedido($registro)
    {
        $ultimo_id = $registro ? $registro->id + 1 : 1;
        $numero_pedido = date('Ymd') . '-' . str_pad($ultimo_id, 3, '0', STR_PAD_LEFT);
        return $numero_pedido;
    }


    /**
     * Genera el número de proforma basado en la fecha actual y el último ID
     * @param ProformaAdecentamiento|null $proforma
     * @return string $numero_proforma
     */
    function numeroProforma($numero)
    {
        $numero_proforma = date('Ymd') . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
        return $numero_proforma;
    }

    /**
     * Obtiene los clientes activos para un select
     * @return Collection
     */
    function getClientes()
    {
        $clientes = Cliente::where('activo', true)->pluck('nombre', 'id');
        $clientes->prepend('', '');
        return $clientes;
    }

    function getProdutosProformas($tipo_proforma)
    {
        $tipo = $tipo_proforma == 'adecentamientos' ? 'tipo.proformas.adecentamientos' : 'tipo.proformas.diseno.planos';
        $tipoId = CatalogoDato::getIdCatalogo($tipo);
        $productos = ProformaProducto::where('categoria_id', $tipoId)->where('activo', true)->pluck('nombre', 'id');
        $productos->prepend('', '');
        return $productos;
    }
}
/**
 * Calcula la fecha final por semanas de plazo
 * @param fecha_inicio 
 * @param semanasPlazo
 * return fecha_final
 */
if (!function_exists('calcularFechaFinal')) {
    function calcularFechaFinal($fechaInicio, $semanasPlazo)
    {
        // Convertimos el plazo en días laborables (5 días por semana)
        $diasLaborables = $semanasPlazo * 5;

        // Creamos una instancia de Carbon para la fecha de inicio
        $fechaFinal = Carbon::parse($fechaInicio);

        // Bucle para avanzar solo en días laborables
        while ($diasLaborables > 0) {
            $fechaFinal->addDay(); // Avanza un día

            // Si es un día laborable (ni sábado ni domingo), restamos un día laborable
            if ($fechaFinal->isWeekday()) {
                $diasLaborables--;
            }
        }

        return $fechaFinal->toDateString(); // Devuelve la fecha final como cadena
    }

    function quitarSimboloUSD($precioConSimbolo)
    {
        $precioLimpio = preg_replace('/[^0-9.]/', '', $precioConSimbolo);
        return $precioLimpio;
    }

    function plazoSemanasProyecto($fecha_inicio, $fecha_fin)
    {
        $fechaInicio = Carbon::parse($fecha_inicio);
        $fechaFin = Carbon::parse($fecha_fin);
        // Calcular la diferencia en días
        $diferenciaEnDias = $fechaInicio->diffInDays($fechaFin);

        // Redondear hacia arriba para incluir semanas parciales
        $semanas = ceil($diferenciaEnDias / 7);
        return $semanas;
    }

    function fechasSemana($fecha_inicio, $fecha_fin)
    {
        $fechaInicio = Carbon::parse($fecha_inicio);
        $fechaFin = Carbon::parse($fecha_fin);
        $semanas = [];

        while ($fechaInicio->lessThanOrEqualTo($fechaFin)) {
            $semanas[] = $fechaInicio->format('Y-m-d'); // Guardar la fecha de inicio de la semana
            $fechaInicio->addWeek(); // Sumar una semana
        }
        return $semanas;
    }

    function calcularMesesEntreFechas($fechaInicio, $fechaFin)
    {
        // Convertir las fechas a objetos Carbon
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);

        // Calcular la diferencia en meses
        return $inicio->diffInMonths($fin);
    }
}

if (!function_exists('palabras')) {
    function palabras()
    {
        $palabras = DiccionarioPalabra::pluck('palabra', 'id');
        return $palabras;
    }

    function agregarPalabra($palabra)
    {
        $existe = DiccionarioPalabra::where('palabra', $palabra)->exists();
        if (!$existe) {
            DiccionarioPalabra::create(['palabra' => $palabra]);
        }
    }

    function getJPLimpiezaNecesidades()
    {
        $jp_limpieza = DetalleAdquisicion::groupBy('necesidad')->pluck('necesidad', 'necesidad');
        $jp_limpieza->prepend('', '');
        return $jp_limpieza;
    }

    function logoBase64($logo = 'prime')
    {
        $path = $logo != 'prime' ? 'images/logo_limpieza.jpeg' : 'images/logo_empresa.jpg';

        $rutaImagen = public_path($path);

        if (!file_exists($rutaImagen)) {
            return false;
        }

        $tipo_mime = mime_content_type($rutaImagen);
        $contenido_base64 = base64_encode(file_get_contents($rutaImagen));

        return "data:$tipo_mime;base64,$contenido_base64";
    }

    // Función para limpiar un valor
    function limpiarValor($valor)
    {
        return floatval(preg_replace('/[^0-9.-]/', '', $valor)); // Elimina "$", ",", etc.
    }

    function usuariosPluck($viewAll = false, $prepend = true)
    {
        $has_role = auth()->user()->hasRole('Administrador');
        if ($has_role) {
            $usuarios = User::where('id', '!=', auth()->user()->id)->pluck('nombre', 'id');
        } elseif ($viewAll) {
            $usuarios = User::whereHas('roles', function ($query) {
                $query->where('name', '!=', 'Administrador');
            })->orderBy('nombre', 'asc')->pluck('nombre', 'id');
        } else {
            $usuarios = User::where('id', '!=', auth()->user()->id)->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'Administrador');
            })->pluck('nombre', 'id');
        }
        // Add an empty option at the beginning
        if ($prepend) {
            $usuarios->prepend('', '');
        }
        return $usuarios;
    }


    function pluckDescripcionesResumenPagosSemanales()
    {
        $detalle = DetalleResumenPagoSemanal::groupBy('descripcion')->pluck('descripcion', 'descripcion');
        $detalle->prepend('', '');
        return $detalle;
    }

    function pluckTitulosTareas()
    {
        $titulos = Tarea::groupBy('titulo')->pluck('titulo', 'titulo');
        $titulos->prepend('', '');
        return $titulos;
    }

    function agendaCategoria()
    {
        $categorias = CatalogoDato::getChildrenCatalogo('categorias.agenda')->pluck('descripcion', 'id');
        return $categorias;
    }

    function categoriasAgenda($filtre = false)
    {
        $categorias = CatalogoDato::getChildrenCatalogo('categorias.agenda')->pluck('descripcion', 'id');
        $users = User::where('id', '!=', 1)->pluck('nombre', 'id');

        if ($filtre) {
            $categorias->prepend('todos', 'todos');
            // Combinar las categorías y los usuarios en una sola lista
            $combined = $categorias->union($users);
        } else {
            $combined = $categorias;
        }
        // Prepend an empty option at the beginning
        $combined->prepend('', '');

        return $combined;
    }

    function produtosPluck()
    {
        $productos = Articulo::where('activo', true)->pluck('descripcion', 'id');
        $productos->prepend('', '');
        return $productos;
    }

    function getNecesidades($isSelected = false)
    {
        if ($isSelected) {
            $necesidades = DiccionarioPalabra::orderBy('palabra', 'asc')->pluck('palabra', 'id');
            $necesidades->prepend('', '');
        } else {
            $necesidades = DiccionarioPalabra::orderBy('palabra', 'asc')->get();
        }

        return $necesidades;
    }

    function getProveedores($isSelected = false, $categoria_proveedor = null)
    {
        if ($isSelected) {
            if ($categoria_proveedor) {
                $proveedores = Proveedor::where('categoria_proveedor_id', CatalogoDato::getIdCatalogo($categoria_proveedor))->pluck('razon_social', 'id');
            } else {
                $proveedores = Proveedor::pluck('razon_social', 'id');
            }
            $proveedores->prepend('', '');
        } else {
            $proveedores = Proveedor::get();
        }
        return $proveedores;
    }

    function getEstadosTarea()
    {
        $estados = CatalogoDato::getChildrenCatalogo('estados.tarea')->pluck('descripcion', 'id');
        $estados->prepend('', '');
        return $estados;
    }

    function getEstadosSolicitudes()
    {
        $estados = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');
        $estados->prepend('', '');
        return $estados;
    }

    function getProdutosJPLimpieza()
    {
        $productos = Producto::where('activo', true)->pluck('nombre', 'id');
        $productos->prepend('', '');
        return $productos;
    }

    /**
     * Agregar un nuevo producto a la base de datos JPLimpieza
     * @param string $nombre
     */
    function agregarProducto($nombre, $valor = null, $iva = null, $unidad_medida = null)
    {
        // Verificar si el producto ya existe
        $productoExistente = Producto::where('nombre', $nombre)->first();
        if ($productoExistente) {
            return $productoExistente->id;
        }

        // Registrar la unidad de medida si no existe
        if ($unidad_medida) {
            $unidad_medida_id = registrarUnidadMedida($unidad_medida);
        } else {
            $unidad_medida_id = null;
        }

        // Crear el nuevo producto
        $nuevoProducto = Producto::create([
            'nombre' => $nombre,
            'precio_unitario' => $valor,
            'iva' => $iva,
            'unidad_medida_id' => $unidad_medida_id,
            'activo' => true,
        ]);

        return $nuevoProducto->id;
    }

    function agregarProductoProforma($nombre, $valor, $tipo, $iva = null, $unidad_medida = null, $descripcion = null)
    {
        // Verificar si el producto ya existe
        $productoExistente = ProformaProducto::where('nombre', $nombre)->first();
        if ($productoExistente) {
            return $productoExistente->id;
        }

        // Registrar la unidad de medida si no existe
        if ($unidad_medida) {
            $unidad_medida_id = registrarUnidadMedida($unidad_medida);
        } else {
            $unidad_medida_id = null;
        }

        // Crear el nuevo producto
        $nuevoProducto = ProformaProducto::create([
            'categoria_id' => CatalogoDato::getIdCatalogo($tipo == 'adecentamientos' ? 'tipo.proformas.adecentamientos' : 'tipo.proformas.diseno.planos'),
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => limpiarValor($valor),
            'iva' => limpiarValor($iva),
            'precio_final' => calcularTotalProducto(1, limpiarValor($valor), limpiarValor($iva)),
            'unidad_medida_id' => $unidad_medida_id,
            'activo' => true,
        ]);

        return $nuevoProducto->id;
    }

    function getCategoriasPresupuesto()
    {
        $categorias = PlantillaPresupuesto::whereNull('padre_id')
            ->where('activo', true)
            ->get();

        return $categorias;
    }

    function getTipoPropiedadesVenta()
    {
        $tipos = CatalogoDato::getChildrenCatalogo('tipo.propiedades')->pluck('descripcion', 'id');
        $tipos->prepend('', '');
        return $tipos;
    }

    function getCiudades()
    {
        $ciudades = Cliente::groupBy('ciudad')->pluck('ciudad', 'ciudad');
        $ciudades->prepend('', '');
        return $ciudades;
    }

    function getInfoEmpresa()
    {
        $empresa = CatalogoDato::getChildrenCatalogo('informacion.general');
        return $empresa;
    }

    function calcularTotalProforma($subtotal, $iva = 0, $descuento = 0)
    {
        if ($descuento > 0) {
            $subtotal = $subtotal - ($subtotal * $descuento / 100);
        }
        if ($iva > 0) {
            $iva = $subtotal * $iva / 100;
        }

        $total = $subtotal + $iva;

        return $total;
    }

    /**
     * Calcular el porcentaje del costo indirecto del precio unitario
     * @param float valor
     */
    function calcularProcentaje($valor, $porcentaje, $presicion = 2)
    {
        $precio = $valor / (1 - ($porcentaje / 100));
        return round($precio, $presicion);
    }
}