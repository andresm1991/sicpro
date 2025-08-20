<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\JPLimpieza\PlantillaPresupuesto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlantillaPresupuestoJPLimpiezaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $datos = [
            [
                'descripcion' => 'PERSONAL',
                'detalle' => '',
                'slug' => 'personal',
                'hijos' => ['Auxiliares', 'Supervisor'],
            ],
            [
                'descripcion' => 'UNIFORMES Y EPP',
                'detalle' => '(Se entrega una vez al iniciar el contrato)',
                'slug' => 'uniformes.epp',
                'hijos' => [
                    'Camisas',
                    'Pantalon',
                    'Cofia',
                    'Mascarilla',
                    'Guantes',
                    'Botas',
                    'Mandil',
                    'Credencial',
                ],
            ],
            [
                'descripcion' => 'HERRAMIENTAS',
                'detalle' => '(Se entrega una vez al iniciar el contrato)',
                'slug' => 'herramientas',
                'hijos' => [
                    'COCHE',
                    'MOPA PISO',
                    'MOPA PARED',
                    'TRAPEADOR',
                    'CEPILLO',
                    'BROCHA',
                    'ESPATULA',
                    'MANO DE OSO',
                    'DESTAPACAÑO',
                    'LIMPIAVIDRIOS',
                    'ESCOBA CERDA SYAVE',
                    'ESCIBA CERDA DYRA',
                    'ESCOBILLON',
                    'PAÑOS',
                    'ATOMIZADOR',
                ],
            ],
            [
                'descripcion' => 'MAQUINARIA',
                'detalle' => '(Se entrega una vez al iniciar el contrato)',
                'slug' => 'maquinaria',
                'hijos' => [
                    'Desbrozadora',
                    'Fumigadora',
                    'Hidrolavadora',
                    'Bomba',
                ],
            ],
            [
                'descripcion' => 'MATERIALES',
                'detalle' => '(Se entrega mensualmente)',
                'slug' => 'materiales',
                'hijos' => [
                    'PAPEL',
                    'TOALLA',
                    'JABON',
                    'DESINFECTANTE',
                    'AMBIENTAL',
                    'CLORO',
                    'ALCOHOL',
                    'LIMPIAVIDRIOS',
                    'ANTIZARRO',
                    'TIPS',
                    'SPRAY',
                    'MATASANCUOS',
                    'FUNDAS NEGRA P',
                    'FUNDAS NEGRA P',
                    'FUNDAS NEGRA I',
                    'FUNDA NEGRA YUMBO',
                    'FUNDAS ROJA P',
                    'FUNDAS ROJA I',
                    'FUNDA ROJA YUMBO',
                    'ESPONJA',
                    'LIJA',
                    'FIBRA VERDE',
                    'LAVAVAJILLA',
                    'DETERGENTE',
                    'ABONO',
                    'MATA HORMIGA',
                    'GLIFOSATO',
                    'AMINA',
                    'QUEMANTE',
                    'UREA',

                ],
            ],
            [
                'descripcion' => 'COMBUSTIBLES',
                'detalle' => '(Se entrega mensualmente)',
                'slug' => 'combustibles',
                'hijos' => [
                    'Gasolina para hidrolavadora',
                    'Gasolina para chapeadora',
                    'Aceite 2t',

                ],
            ],
            [
                'descripcion' => 'MOVILIZACION',
                'detalle' => '(Se entrega mensualmente)',
                'slug' => 'movilizacion',
                'hijos' => [
                    'Entregas de herramientas',
                    'Entregas de materiales',
                    'Entregas de documentos',
                    'Recepcion de documentos',
                    'Varias',
                ],
            ],
            [
                'descripcion' => 'SEGURO',
                'detalle' => '(Se entrega una vez al iniciar el contrato)',
                'slug' => 'seguro',
                'hijos' => [
                    'Poliza de fiel cumplimiento',
                    'poliza anticipo de garantia',
                ],
            ]
        ];

        foreach ($datos as $categoria) {
            $padreId = PlantillaPresupuesto::create([
                'descripcion' => $categoria['descripcion'],
                'detalle' => $categoria['detalle'],
                'slug' => $categoria['slug'],
                'padre_id' => null,
                'activo' => true,
            ])->id;

            foreach ($categoria['hijos'] as $hijo) {
                PlantillaPresupuesto::create([
                    'descripcion' => $hijo,
                    'detalle' => '',
                    'slug' => $categoria['slug'] . '.' . Str::slug($hijo),
                    'padre_id' => $padreId,
                    'activo' => true,
                ]);
            }
        }
    }
}