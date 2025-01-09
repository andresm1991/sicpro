<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoManoObra extends Model
{
    use HasFactory;

    protected $table = 'pagos_mano_obra';
    protected $fillable = ['mano_obra_id', 'pago_prestamo_id'];

    public function mano_obra()
    {
        return $this->belongsTo(ManoObra::class, 'mano_obra_id');
    }

    public function pago_prestamo()
    {
        return $this->belongsTo(PagoPrestamo::class, 'pago_prestamo_id');
    }
}