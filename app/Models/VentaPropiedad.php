<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaPropiedad extends Model
{
    use HasFactory;

    protected $table = 'venta_propiedades';
    protected $fillable = [
        'nombre',
        'direccion',
        'area',
        'telefono',
        'correo',
        'latitud',
        'longitud',
        'precio_venta',
        'precio_por_metros_cuadrados',
        'estado',
        'frente',
        'fondo',
        'tipo_propiedad_id',
        'observaciones',
    ];

    protected $appends = ['created_at_formatted', 'precio_venta_formatted', 'area_formatted'];

    public function imagenes_propiedades()
    {
        return $this->hasMany(ImagenPropiedad::class);
    }

    public function getPrecioVentaFormattedAttribute()
    {
        return number_format($this->precio_venta, 2);
    }
    public function getPrecioPorMetroCuadradoFormattedAttribute()
    {
        return number_format($this->precio_por_metros_cuadrados, 2);
    }
    public function getAreaFormattedAttribute()
    {
        return number_format($this->area, 0);
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('Y-m-d');
    }
}