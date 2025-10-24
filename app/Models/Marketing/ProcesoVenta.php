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
        'Entrega',
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
        return $this->hasMany(Contrato::class);
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

    public function isEtapaCompleta(string $etapa): bool
    {
        // Buscamos el índice (la posición) de la etapa actual del proceso
        $indiceActual = array_search($this->etapa_actual, self::ETAPAS);

        // Buscamos el índice de la etapa que queremos comparar
        $indiceAComparar = array_search($etapa, self::ETAPAS);

        // Si alguna de las etapas no se encuentra en la lista, devolvemos false para evitar errores
        if ($indiceActual === false || $indiceAComparar === false) {
            return false;
        }

        // La etapa está completa si el índice actual es mayor o igual al índice a comparar
        return $indiceActual >= $indiceAComparar;
    }
}