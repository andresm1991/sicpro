<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoDato extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'catalogo_datos';

    protected $fillable = ['descripcion', 'detalle', 'slug', 'padre_id', 'activo'];

    public function childs()
    {
        return $this->hasMany(CatalogoDato::class, 'padre_id', 'id');
    }

    public function catalogo_datos()
    {
        return $this->hasMany(CatalogoDato::class, 'padre_id');
    }

    public function childrenCatalogoDatos()
    {
        return $this->hasMany(CatalogoDato::class, 'padre_id')->with('catalogo_datos');
    }

    public static function getCatalogoPadre($value)
    {
        return CatalogoDato::where('slug', $value)->first();
    }

    public static function getChildrenCatalogo($value, $slugsADescartar = [])
    {
        $catalogo = CatalogoDato::where('slug', $value)->first();

        // Helper compartido por getInfoEmpresa() y decenas de controladores: si
        // el nodo padre no existe se devuelve una coleccion vacia en lugar de
        // reventar con "Attempt to read property \"id\" on null".
        if (!$catalogo) {
            return new Collection();
        }

        return CatalogoDato::where('padre_id', $catalogo->id)
            ->whereNotIn('slug', $slugsADescartar)
            ->get();
    }

    public static function getEstadoInventarioId($slug)
    {
        $catalogo = CatalogoDato::where('slug', $slug)->first();
        return $catalogo->id;
    }

    public static function getIdCatalogo($slug)
    {
        $catalogo = CatalogoDato::where('slug', $slug)->first();
        return $catalogo->id;
    }
}