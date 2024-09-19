<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManoObra extends Model
{
    use HasFactory;
    protected $table = 'mano_obra';
    protected $fillable = [
        'fecha', 'proyecto_id', 'proveedor_id', 'articulo_id', 'etapa_id', 'tipo_etapa_id', 'usuario_id', 'jornada', 'valor', 'adicional', 'descuento', 'detalle_adicional', 'detalle_descuento', 'observacion'
    ];

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
}
