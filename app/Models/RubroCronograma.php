<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubroCronograma extends Model
{
    use HasFactory;
    protected $table =  'rubro_cronogramas';
    protected $fillable = ['descripcion', 'activo'];
}
