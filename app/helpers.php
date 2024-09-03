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
if (!function_exists('showImage')) {
    function showImage($fileName)
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
