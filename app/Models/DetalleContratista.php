<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleContratista extends Model
{
    use HasFactory;
    protected $table = 'detalle_contratistas';
    protected $fillable = ['contratista_id', 'articulo_id', 'cantidad', 'unidad_medida_id', 'valor_unitario'];

    public function contratista()
    {
        return $this->belongsTo(Contratista::class, 'contratista_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function unidad_medida()
    {
        return $this->belongsTo(CatalogoDato::class, 'unidad_medida_id');
    }
}
