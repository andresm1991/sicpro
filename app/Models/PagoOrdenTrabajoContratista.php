<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoOrdenTrabajoContratista extends Model
{
    use HasFactory;
    protected $table = 'pagos_orden_trabajo_contratista';
    protected $fillable = ['fecha', 'contratista_id', 'tipo_pago', 'forma_pago', 'valor', 'detalle'];

    public function contratista() {
        return $this->belongsTo(Contratista::class, 'contratista_id');
    }

}
