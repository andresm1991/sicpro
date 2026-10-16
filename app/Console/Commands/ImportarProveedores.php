<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportarProveedores extends Command
{
    protected $signature = 'importar:proveedores {file}';
    protected $description = 'Importa proveedores desde un archivo CSV y los compara con la base de datos';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: $filePath");
            return;
        }

        // Abrir el archivo CSV con el delimitador correcto (;)
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ";"); // Usamos ; como delimitador

            // Iterar sobre cada fila del archivo CSV
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $row = array_combine($header, $data); // Combina encabezados con valores
                print_r($row);
                $numeroDocumento = $row['numero_documento'];
                $razonSocial = mb_convert_encoding($row['razon_social'], 'UTF-8', 'auto');
                $correo = $this->cleanString($row['correo']);
                $direccion =  $this->cleanString($row['direccion']);

                // Verificar si el proveedor ya existe
                $proveedor = Proveedor::where('documento', $numeroDocumento)->first();

                if (!$proveedor) {
                    // Insertar el nuevo proveedor
                    Proveedor::create([
                        'categoria_proveedor_id' => $row['categoria'],
                        'documento' => $numeroDocumento,
                        'razon_social' => $razonSocial,
                        'telefono' => $row['telefono'],
                        'correo' => $correo,
                        'direccion' => $direccion,
                        // Agrega otros campos que sean necesarios
                    ]);

                    $this->info("Proveedor con documento $numeroDocumento ha sido insertado.");
                } else {
                    $this->info("Proveedor con documento $numeroDocumento ya existe.");
                }
            }

            fclose($handle);
        }

        $this->info('Proceso completado.');
    }

    // Función para limpiar cadenas eliminando caracteres no válidos
    private function cleanString($string)
    {
        // Convertir a UTF-8 y reemplazar caracteres específicos
        $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');

        // Reemplazar caracteres problemáticos comunes
        $string = str_replace('�', 'Í', $string); // Ejemplo de reemplazo de � con Í

        // Otros reemplazos que pudieran ser necesarios
        $string = str_replace('�', 'O', $string); // Reemplaza el carácter si aparece en otro contexto

        return trim($string); // Limpiar espacios en blanco
    }
}
