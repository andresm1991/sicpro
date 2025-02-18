<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cronograma extends Model
{
    use HasFactory;
    protected $table = 'cronograma';
    protected $fillable = ['proyecto_id', 'rubro_cronograma_id', 'semana', 'dia', 'observacion'];

    public function rubro_cronograma()
    {
        return $this->belongsTo(RubroCronograma::class, 'rubro_cronograma_id');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
}
