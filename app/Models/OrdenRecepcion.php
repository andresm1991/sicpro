<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenRecepcion extends Model
{
    use HasFactory;

    protected $table = 'orden_recepciones';
    protected $fillable = ['fecha', 'adquisicion_id', 'proveedor_id', 'forma_pago_id', 'completado', 'editar'];

    public function forma_pago()
    {
        return $this->belongsTo(CatalogoDato::class, 'forma_pago_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
