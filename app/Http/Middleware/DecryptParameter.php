<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class DecryptParameter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Desencriptar parámetros de entrada
        $routeParameters = $request->route()->parameters();
        $decryptedParameters = [];

        foreach ($routeParameters as $key => $value) {
            if (is_string($value)) {
                try {
                    $decryptedParameters[$key] = Crypt::decrypt($value);
                } catch (\Exception $e) {
                    // Manejar fallo de desencriptación
                    Log::error('Desencriptación fallida para el parámetro: ' . $key);
                    $decryptedParameters[$key] = $value;
                }
            } else {
                $decryptedParameters[$key] = $value;
            }
        }

        foreach ($decryptedParameters as $key => $value) {
            $request->route()->setParameter($key, $value);
        }
        return $next($request);
    }
}