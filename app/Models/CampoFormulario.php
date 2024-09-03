<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampoFormulario extends Model
{
    use HasFactory;
    protected $table = 'campos_formulario';
    protected $fillable = ['formulario_id', 'lable', 'tipo', 'requerido', 'orden', 'instrucciones',];
}