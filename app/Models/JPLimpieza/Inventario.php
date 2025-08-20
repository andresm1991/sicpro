<?php

namespace App\Models\JPLimpieza;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'inventario';

    protected $fillable = [
        'adquisicion_id',
        'producto_id',
        'cantidad',
        'cantidad_debaja',
        'fecha_ingreso',
        'fecha_debaja',
        'usuario_id',
        'estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_debaja' => 'date',
    ];

    public function adquisicion()
    {
        return $this->belongsTo(Adquisicion::class, 'adquisicion_id');
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}