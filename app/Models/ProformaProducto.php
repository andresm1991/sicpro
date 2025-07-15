<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaProducto extends Model
{
    use HasFactory;
    protected $table = 'proforma_productos';
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'iva',
        'precio_final',
        'unidad_medida_id',
        'activo',
        'codigo',
        'observaciones'
    ];

    public function unidadMedida()
    {
        return $this->belongsTo(CatalogoDato::class, 'unidad_medida_id');
    }

    public function getPrecioAttribute()
    {
        return $this->precio;
    }

    public function getPrecioFinalAttribute()
    {
        return $this->precio + ($this->precio * $this->iva / 100);
    }

    public function getPrecioFormatAttribute()
    {
        return number_format($this->precio, 2, ',', '.');
    }

    public function getPrecioFinalFormatAttribute()
    {
        return number_format($this->precio_final, 2, ',', '.');
    }
}