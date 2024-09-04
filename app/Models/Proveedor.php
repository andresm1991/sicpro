<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';
    protected $fillable = [
        'categoria_proveedor_id',
        'documento',
        'razon_social',
        'telefono',
        'correo',
        'direccion',
        'banco_id',
        'tipo_cuenta_id',
        'numero_cuenta',
        'observacion',
        'calificacion'
    ];

    public function proveedor_articulos()
    {
        return $this->hasMany(ProveedorArticulo::class);
    }
}