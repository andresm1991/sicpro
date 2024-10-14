<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiccionarioPalabra extends Model
{
    use HasFactory;
    protected $table = 'diccionario_palabras';
    protected $fillable = ['palabra'];
}