<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Datos de la empresa para los documentos (getDatosEmpresa).
 *
 * La proforma los leia por posicion ($empresa[0..4]) sobre los hijos de
 * 'informacion.general': si el catalogo venia incompleto, con el orden
 * cambiado o con un duplicado (en produccion hay DOS filas de RUC), el
 * documento mostraba el dato equivocado o reventaba.
 */
class DatosEmpresaTest extends TestCase
{
    use RefreshDatabase;

    private function padre(string $slug = 'informacion.general'): CatalogoDato
    {
        return CatalogoDato::create([
            'descripcion' => 'Informacion General',
            'detalle' => '',
            'slug' => $slug,
            'padre_id' => null,
            'activo' => true,
        ]);
    }

    private function hijo(CatalogoDato $padre, string $slug, string $detalle): CatalogoDato
    {
        return CatalogoDato::create([
            'descripcion' => $slug,
            'detalle' => $detalle,
            'slug' => $slug,
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
    }

    public function test_resuelve_cada_dato_por_su_slug_y_no_por_su_posicion(): void
    {
        $padre = $this->padre();

        // Orden invertido al de la vista y con el RUC duplicado, como en produccion.
        $this->hijo($padre, 'informacion.general.ruc', '2390034515001');
        $this->hijo($padre, 'informacion.general.correo', 'c.primejp@gmail.com');
        $this->hijo($padre, 'informacion.general.telefono', '022753566 / 0997457053');
        $this->hijo($padre, 'informacion.general.direccion', 'Av. 6 de Noviembre 722 y Augusto Gachet');
        $this->hijo($padre, 'informacion.general.nombre.empresa', 'CONSTRUCCIONES PRIME JP S.A');
        $this->hijo($padre, 'informacion.general.ruc', '2390034515001');

        $this->assertSame([
            'nombre' => 'CONSTRUCCIONES PRIME JP S.A',
            'direccion' => 'Av. 6 de Noviembre 722 y Augusto Gachet',
            'telefono' => '022753566 / 0997457053',
            'correo' => 'c.primejp@gmail.com',
            'ruc' => '2390034515001',
        ], getDatosEmpresa());
    }

    public function test_un_catalogo_incompleto_no_rompe_y_deja_los_faltantes_vacios(): void
    {
        $padre = $this->padre();
        $this->hijo($padre, 'informacion.general.nombre.empresa', 'CONSTRUCCIONES PRIME JP S.A');
        $this->hijo($padre, 'informacion.general.ruc', '2390034515001');

        $datos = getDatosEmpresa();

        $this->assertSame('CONSTRUCCIONES PRIME JP S.A', $datos['nombre']);
        $this->assertSame('2390034515001', $datos['ruc']);
        $this->assertSame('', $datos['direccion']);
        $this->assertSame('', $datos['telefono']);
        $this->assertSame('', $datos['correo']);
    }

    public function test_sin_el_nodo_padre_devuelve_todo_vacio(): void
    {
        $this->assertSame([
            'nombre' => '',
            'direccion' => '',
            'telefono' => '',
            'correo' => '',
            'ruc' => '',
        ], getDatosEmpresa());
    }
}
