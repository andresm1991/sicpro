<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\CatalogoDato;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ItemsSeguimientoVentasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'Cédula y Papeleta de votación comprador',
            'Cédula y Papeleta de votación comprador conyuge (si es que aplica)',
            'planilla de servicio basico',
            'Roles de pago',
            'certificado laboral',
            'Certificado de bienes',
            'certificado bancario',
            'estado de cuenta',
            'RUC (Registro Único de Contribuyentes)',
            'Cédula y Papeleta de votación representante legal',
            'Nombramiento del representante legal de la empresa',
            'Escritura pública de constitución de la Compañía',
            'Patente del vendedor vigente',
            'Acta de Junta de Socios autorizando la venta del inmueble',
            'Carta de pago impuesto predial del año en curso',
            'Ficha Catastral e informe de regulación urbana',
            'Certificado de avaluo',
            'Certificado de Gravámenes vigente',
            'Certificado de expensas',
            'Certificado bancario de la cuenta de ahorros o corriente',
            'Escritura de compra venta',
            'Escritura de unificacion',
            'Escritura Pública de Declaratoria de Propiedad Horizontal',
            'Permiso de construccion',
            'Aprobacion de planos estructurales',
            'Aprobacion de planos arquitectonicos',
            'Registro Miduvi',
        ];

        $padre = CatalogoDato::create([
            'descripcion' => 'Items Documentos Proceso Venta',
            'detalle' => '',
            'slug' => 'items.documentos.proceso.ventas',
            'padre_id' => null,
            'activo' => true,
        ]);

        foreach ($items as $value) {
            CatalogoDato::create([
                'descripcion' => $value,
                'detalle' => '',
                'slug' => 'tems.documentos.proceso.ventas.' . Str::slug($value),
                'padre_id' => $padre->id,
                'activo' => true,
            ]);
        }
    }
}
