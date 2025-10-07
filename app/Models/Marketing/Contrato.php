<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;
    protected $table = 'contratos';
    protected $fillable = ['proceso_venta_id', 'titulo', 'contenido'];

    public function procesoVenta()
    {
        return $this->belongsTo(ProcesoVenta::class);
    }
}
