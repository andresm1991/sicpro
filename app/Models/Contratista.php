<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contratista extends Model
{
    use HasFactory;
    protected $table = 'contratistas';
    protected $fillable = ['fecha', 'proveedor_id', 'articulo_id', 'proyecto_id', 'etapa_id', 'tipo_etapa_id', 'usuario_id', 'estado_id'];

    public function detalle_contratistas()
    {
        return $this->hasMany(DetalleContratista::class);
    }

    public function pagosOrdenTrabajoContratista(){
        return $this->hasMany(PagoOrdenTrabajoContratista::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function etapa()
    {
        return $this->belongsTo(CatalogoDato::class, 'etapa_id');
    }

    public function tipo_etapa()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_etapa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }
    public function getTotalContratistasAttribute()
    {
        return $this->detalle_contratistas()->selectRaw('SUM(cantidad * valor_unitario) as total')->pluck('total')->first();
    }

    public function getPagosContratistasAttribute()
    {
        return $this->pagosOrdenTrabajoContratista()->selectRaw('SUM(valor) as pagos')->pluck('pagos')->first();
    }

    public function getNumeroPagoContratistaAttribute()
    {
        return $this->pagosOrdenTrabajoContratista()
        ->selectRaw('id')
        ->latest('id')
        ->first();
    }

    public function getTipoPagoContratistaAttribute()
    {
        return $this->pagosOrdenTrabajoContratista()
        ->selectRaw('tipo_pago')
        ->latest('id')
        ->pluck('tipo_pago')
        ->first();
    }
}
