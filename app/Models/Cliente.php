<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes';
    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'ciudad',
        'activo',
        'tipo_cliente',
        'ruc',
        'contacto',
        'telefono_contacto',
        'email_contacto',
        'observaciones'
    ];
}