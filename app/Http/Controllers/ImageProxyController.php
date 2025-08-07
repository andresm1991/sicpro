<?php

namespace App\Http\Controllers;

use App\Models\ImagenPropiedad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageProxyController extends Controller
{
    /**
     * Obtiene una URL pre-firmada para un archivo en DigitalOcean Spaces
     * y redirige al usuario a ella.
     *
     * @param string $filename El nombre del archivo a buscar.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getPropertyImage(ImagenPropiedad $imagen, $filename)
    {
        // 1. Decodificar el nombre del archivo desde base64.
        $filePath = $imagen->path_file;
        if (!Storage::disk('digitalocean')->exists($filePath)) {
            // Si el archivo no se encuentra, devuelve un error 404 (No encontrado).
            abort(404, 'La imagen solicitada no existe.');
        }

        // 2. Generar la URL pre-firmada (temporal y segura) desde DigitalOcean.
        // La validez puede ser corta, ya que solo se usa para la redirección inicial.
        $presignedUrl = Storage::disk('digitalocean')->temporaryUrl(
            $filePath,
            now()->addMinutes(5) // La URL será válida por 5 minutos.
        );


        // 3. Redirigir el navegador del usuario a la URL de DigitalOcean.
        // El navegador seguirá esta redirección y cargará la imagen.
        // Dropify recibirá la imagen sin problemas.
        return redirect()->away($presignedUrl);
    }
}