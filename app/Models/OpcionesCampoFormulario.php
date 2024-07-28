<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpcionesCampoFormulario extends Model
{
    use HasFactory;
    protected $table = 'opciones_campos_formulario';
    protected $fillable = ['campo_formulario_id', 'valor', 'texto',];
}