<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenPropiedad extends Model
{
    use HasFactory;
    protected $table = 'imagenes_propiedades';

    protected $fillable = [
        'venta_propiedad_id',
        'file_name',
        'path_file',
    ];

    public function venta_propiedades()
    {
        return $this->belongsTo(VentaPropiedad::class, 'venta_propiedad_id');
    }
}