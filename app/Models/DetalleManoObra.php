<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleManoObra extends Model
{
    use HasFactory;
    protected $table = 'detalle_mano_obra';
    protected $fillable = [
        'fecha',
        'mano_obra_id',
        'proveedor_id',
        'articulo_id',
        'jornada',
        'valor',
        'adicional',
        'descuento',
        'detalle_adicional',
        'detalle_descuento',
        'observacion'
    ];

    public function mano_obra()
    {
        return $this->belongsTo(ManoObra::class, 'mano_obra_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function proveedor_articulos()
    {
        return $this->belongsTo(ProveedorArticulo::class, 'articulo_id');
    }
}
