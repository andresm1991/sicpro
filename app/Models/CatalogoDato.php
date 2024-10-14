<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoDato extends Model
{
    use HasFactory;
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

    public static function getChildrenCatalogo($value)
    {
        $catalogo = CatalogoDato::where('slug', $value)->first();
        return CatalogoDato::where('padre_id', $catalogo->id)->get();
    }

    public static function getEstadoInventarioId($slug)
    {
        $catalogo = CatalogoDato::where('slug', $slug)->first();
        return $catalogo->id;
    }
}