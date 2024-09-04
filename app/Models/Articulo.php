<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    use HasFactory;
    protected $table = 'articulos';

    protected $fillable = [
        'categoria_id',
        'codigo',
        'descripcion',
        'activo',
    ];

    public function categoria_articulo()
    {
        return $this->belongsTo(CatalogoDato::class, 'categoria_id');
    }

    public function proveedor_articulos()
    {
        return $this->hasMany(ProveedorArticulo::class);
    }
}