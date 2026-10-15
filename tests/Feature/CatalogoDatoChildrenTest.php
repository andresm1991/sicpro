<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CatalogoDato::getChildrenCatalogo es un helper compartido por getInfoEmpresa()
 * y decenas de controladores. Resolvia el padre con first() y enseguida usaba
 * $catalogo->id, asi que un nodo padre ausente tiraba
 * ErrorException: Attempt to read property "id" on null -> 500.
 */
class CatalogoDatoChildrenTest extends TestCase
{
    use RefreshDatabase;

    private function padre(string $slug): CatalogoDato
    {
        return CatalogoDato::create([
            'descripcion' => 'Padre ' . $slug,
            'detalle' => '',
            'slug' => $slug,
            'padre_id' => null,
            'activo' => true,
        ]);
    }

    private function hijo(CatalogoDato $padre, string $slug, string $descripcion): CatalogoDato
    {
        return CatalogoDato::create([
            'descripcion' => $descripcion,
            'detalle' => '',
            'slug' => $slug,
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
    }

    public function test_devuelve_una_coleccion_vacia_cuando_el_padre_no_existe(): void
    {
        $hijos = CatalogoDato::getChildrenCatalogo('padre.inexistente');

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $hijos);
        $this->assertTrue($hijos->isEmpty());
    }

    public function test_devuelve_exactamente_los_hijos_del_padre_cuando_existe(): void
    {
        $padre = $this->padre('menu.adquisiciones');
        $esperado = $this->hijo($padre, 'menu.adquisiciones.uno', 'Uno');
        $this->hijo($padre, 'menu.adquisiciones.dos', 'Dos');
        $ajeno = $this->padre('otro.menu');
        $this->hijo($ajeno, 'otro.menu.uno', 'Ajeno');

        $hijos = CatalogoDato::getChildrenCatalogo('menu.adquisiciones');

        $this->assertSame(['Uno', 'Dos'], $hijos->pluck('descripcion')->all());
        $this->assertSame([$esperado->id], $hijos->where('slug', 'menu.adquisiciones.uno')->pluck('id')->all());
        $this->assertFalse($hijos->contains('descripcion', 'Ajeno'));
    }

    public function test_respeta_los_slugs_descartados(): void
    {
        $padre = $this->padre('menu.adquisiciones');
        $this->hijo($padre, 'menu.adquisiciones.uno', 'Uno');
        $this->hijo($padre, 'menu.adquisiciones.dos', 'Dos');

        $hijos = CatalogoDato::getChildrenCatalogo('menu.adquisiciones', ['menu.adquisiciones.uno']);

        $this->assertSame(['Dos'], $hijos->pluck('descripcion')->all());
    }
}
