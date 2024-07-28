<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;
    protected $table = 'proyectos';
    protected $fillable = [
        'catalogo_proyecto_id',
        'nombre_proyecto',
        'nombre_propietario',
        'ubicacion',
        'direccion',
        'telefono',
        'correo',
        'tipo_proyecto_id',
        'area_lote', 'area_construccion',
        'numero_unidades',
        'area_lote_unidad',
        'area_construccion_unidad',
        'presupuesto_total',
        'presupuesto_unidad',
        'fecha_inicio', 'fecha_finalizacion',
        'observacion',
        'portada',
    ];

    public function tipo_proyecto()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_proyecto_id');
    }

    public function archivos_proyecto()
    {
        return $this->hasMany(ArchivoProyecto::class);
    }
}