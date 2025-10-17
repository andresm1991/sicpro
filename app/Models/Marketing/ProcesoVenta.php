<?php

namespace App\Models\Marketing;

use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcesoVenta extends Model
{
    use HasFactory;

    protected $table = 'proceso_ventas';

    public const ETAPAS = [
        'Reserva',
        'Documentación inicial',
        'Peritaje y aprobación de crédito',
        'Escrituración',
        'Desembolso',
    ];

    protected $fillable = [
        'proyecto_id',
        'unidad',
        'valor_venta',
        'cliente_id',
        'etapa_actual',
        'valor_reserva',
        'contrato_firmado_path',
        'cedula_path',
        'comprobante_pago_reserva_path',
        'visita_perito',
        'aprobacion_credito',
        'observaciones_peritaje',
        'valor_saldo_reserva',
        'comprobante_pago_saldo_reserva_path',
        'monto_desembolsado',
        'observaciones_desembolso',
    ];

    // Castear el campo JSON a un array de PHP automáticamente
    protected $casts = [
        'cedulas_path' => 'array',
        'visita_perito' => 'boolean',
        'aprobacion_credito' => 'boolean',
    ];

    // --- RELACIONES ---

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
    public function cliente()
    {
        return $this->belongsTo(ClienteVenta::class);
    }

    public function documentacionItems()
    {
        return $this->hasMany(DocumentacionItem::class);
    }

    public function escrituracionItems()
    {
        return $this->hasMany(EscrituracionItem::class);
    }

    public function contrato()
    {
        return $this->hasOne(Contrato::class);
    }

    /**
     * Asesores
     */
    public function getFechaAttribute()
    {
        return $this->created_at ? $this->created_at->format('d-m-Y') : null;
    }

    public function getTotalAcreditadoAttribute()
    {
        return $this->valor_reserva + $this->valor_saldo_reserva + $this->monto_desembolsado;
    }
}