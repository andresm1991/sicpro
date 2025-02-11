<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadDiaCronograma extends Model
{
    use HasFactory;
    protected $table = 'actividades_dias_cronograma';
    protected $fillable = ['cronograma_id', 'actividad_cronograma_id', 'dia'];

    public function cronograma()
    {
        return $this->belongsTo(Cronograma::class, 'cronograma_id');
    }

    public function actividad_cronograma()
    {
        return $this->belongsTo(ActividadCronograma::class, 'actividad_cronograma_id');
    }
}