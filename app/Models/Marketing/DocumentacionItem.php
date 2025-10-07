<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentacionItem extends Model
{
    use HasFactory;
    protected $table = 'documentacion_items';

    protected $fillable = [
        'proceso_venta_id',
        'nombre',
        'estado',
        'observaciones',
    ];

    public function procesoVenta()
    {
        return $this->belongsTo(ProcesoVenta::class);
    }
}