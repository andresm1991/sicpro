<?php

use Carbon\Carbon;
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