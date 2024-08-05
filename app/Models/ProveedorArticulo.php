<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProveedorArticulo extends Model
{
    use HasFactory;
    protected $table = 'proveedor_articulos';
    protected $fillable = ['proveedor_id', 'articulo_id'];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}