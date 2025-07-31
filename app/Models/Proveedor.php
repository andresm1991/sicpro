<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'proveedores';
    protected $fillable = [
        'categoria_proveedor_id',
        'documento',
        'razon_social',
        'nombres',
        'apellidos',
        'telefono',
        'correo',
        'direccion',
        'banco_id',
        'tipo_cuenta_id',
        'numero_cuenta',
        'observacion',
        'calificacion'
    ];

    protected $appends = ['nombre_proveedor'];

    public function proveedor_articulos()
    {
        return $this->hasMany(ProveedorArticulo::class);
    }

    public function contratistas()
    {
        return $this->hasMany(Contratista::class);
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }

    public function categoria_proveedor()
    {
        return $this->belongsTo(CatalogoDato::class, 'categoria_proveedor_id');
    }

    public function getNombreProveedorAttribute()
    {
        // trim() elimina espacios y si el resultado es una cadena vacía, se considera falso.
        if (trim($this->razon_social)) {
            return $this->razon_social;
        }

        // Concatena nombres y apellidos, eliminando espacios extra si alguno de los campos está vacío.
        return trim($this->nombres . ' ' . $this->apellidos);
    }
}
