<?php

namespace App\Models\JPLimpieza;

use App\Models\User;
use App\Models\CatalogoDato;
use Illuminate\Database\Eloquent\Model;

class PagoContratista extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'pagos_contratistas';

    protected $fillable = [
        'contratista_id',
        'monto',
        'tipo_pago',
        'forma_pago_id',
        'estado_id',
        'fecha',
        'observaciones',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function contratista()
    {
        return $this->belongsTo(Contratista::class, 'contratista_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleContratista::class, 'contratista_id');
    }

    public function tipoPago()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_pago_id');
    }

    public function formaPago()
    {
        return $this->belongsTo(CatalogoDato::class, 'forma_pago_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getTotalPagadoAttribute()
    {
        $estado = CatalogoDato::getIdCatalogo('estados.pagos.prestamos');
        return $this->where('estado_id', $estado)->sum('monto');
    }

    public function getTotalPagadoFormattedAttribute()
    {
        return number_format($this->monto, 4);
    }


    public function getFechaFormattedAttribute()
    {
        return $this->fecha ? $this->fecha->format('Y-m-d') : null;
    }

    public function getNumeroFormattedAttribute()
    {
        return date('Ymd', strtotime($this->fecha)) . '-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
