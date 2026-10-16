<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOldFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina archivos con una antigüedad mayor a 7 días';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Carpeta donde están los archivos (ajusta según tu configuración)
        $directory = 'adquisiciones'; // Ejemplo: carpeta_archivos/

        // Obtener todos los archivos de la carpeta
        $files = Storage::disk('digitalocean')->allFiles($directory);

        foreach ($files as $file) {
            // Obtener la fecha de última modificación del archivo
            $lastModified = Storage::disk('digitalocean')->lastModified($file);

            // Calcular la diferencia en días
            $daysDifference = now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastModified));

            // Si el archivo tiene más de 7 días, eliminarlo
            if ($daysDifference >= 7) {
                Storage::disk('digitalocean')->delete($file);
                $this->info("Archivo eliminado: {$file}");
            }
        }

        $this->info('Proceso completado.');
    }
}