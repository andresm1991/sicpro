<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadCronograma extends Model
{
    use HasFactory;
    protected $table = 'actividades_cronograma';
    protected $fillable = ['descripcion', 'activo'];
}
