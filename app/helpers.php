<?php

use Carbon\Carbon;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

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
        // Define la duración de validez de la URL firmada
        $expiresAt = Carbon::now()->addMinutes(5);

        // Genera la URL firmada
        $url = Storage::disk('digitalocean')->temporaryUrl($fileName, $expiresAt);

        return $url;
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
    function registrarProducto($tipo, $descripcion)
    {
        $tipo_producto = $tipo->slug == 'meteriales.herramientas' ? 'tipo.adquisiciones.bienes' : 'tipo.adquisiciones.servicios';
        $categoria = CatalogoDato::where('slug', $tipo_producto)->first();
        $type = $tipo->slug == 'meteriales.herramientas' ? 'B-' : 'S-';
        $code = generateProductCode($type);
        $create =  Articulo::create(['categoria_id' => $categoria->id, 'codigo' => $code, 'descripcion' => $descripcion, 'activo' => true]);

        return $create;
    }
}
/**
 * Formato para el numero de orden de trabajo o adquisison
 * @param Model
 * return $numero_orden
 */
if (!function_exists('numeroOrden')) {
    function numeroOrden($numero, $nuevo = true) {
        if($nuevo){
            $ultimo_id = $numero ? $numero->id + 1 : 1;
        }else{
            $ultimo_id = $numero->id;
        }
        
        $numero_orden = date('Ymd') . '-' . str_pad($ultimo_id, 3, '0', STR_PAD_LEFT);
        return $numero_orden;
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
}
