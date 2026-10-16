<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $fillable = ['level', 'message', 'context', 'entidad', 'entidad_id', 'accion', 'valores_antes', 'valores_despues', 'user_id', 'ip'];

    protected $casts = [
        'context' => 'array',
        'valores_antes' => 'array',
        'valores_despues' => 'array',
    ];

    /**
     * Actor del registro. Sin FK a proposito: el visor debe tolerar
     * un usuario eliminado (la relacion devuelve null).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
