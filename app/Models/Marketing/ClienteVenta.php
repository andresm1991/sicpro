<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteVenta extends Model
{
    use HasFactory;
    protected $table = 'clientes_ventas';
    protected $fillable = [
        'nombre',
        'documento',
        'direccion',
        'telefono',
        'email',
        'ciudad',
        'activo',
        'observaciones',
    ];
}
