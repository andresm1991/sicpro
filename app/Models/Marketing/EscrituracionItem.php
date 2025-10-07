<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscrituracionItem extends Model
{
    use HasFactory;

    protected $table = 'escrituracion_items';

    protected $fillable = [
        'proceso_venta_id',
        'nombre',
        'completado',
        'observaciones',
    ];

    protected $casts = [
        'completado' => 'boolean',
    ];

    public function procesoVenta()
    {
        return $this->belongsTo(ProcesoVenta::class);
    }
}