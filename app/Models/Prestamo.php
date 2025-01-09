<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;
    protected $table = 'prestamos';
    protected $fillable = ['trabajador_id', 'fecha_solicitud', 'fecha_aprobacion', 'fecha_vencimiento', 'monto', 'saldo', 'interes', 'plazo', 'estado_id', 'motivo'];

    public function trabajador()
    {
        return $this->belongsTo(Proveedor::class, 'trabajador_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function pago_prestamo()
    {
        return $this->hasMany(PagoPrestamo::class);
    }

    public function pagos_prestamo()
    {
        return $this->hasMany(PagoPrestamo::class)
            ->whereHas('estado', function ($query) {
                $query->where('descripcion', 'Pagado');
            })
            ->whereDoesntHave('pago_mano_obra', function ($query) {
                $query->whereNotNull('pago_prestamo_id'); // Validar que no exista un registro relacionado
            });
    }

    public function obtenerPagosPrestamo()
    {
        return $this->hasMany(PagoPrestamo::class)
            ->whereHas('estado', function ($query) {
                $query->where('descripcion', 'Pagado');
            })
            ->whereHas('pago_mano_obra', function ($query) {
                $query->whereNotNull('mano_obra_id'); // Validar que no exista un registro relacionado
            });
    }

    public function ultimo_pago_prestamo()
    {
        return $this->hasOne(PagoPrestamo::class)
            ->latest('updated_at')
            ->whereHas('estado', function ($query) {
                $query->where('descripcion', 'Pagado');
            })
            ->whereDoesntHave('pago_mano_obra', function ($query) {
                $query->whereNotNull('pago_prestamo_id'); // Validar que no exista un registro relacionado
            });
    }
}