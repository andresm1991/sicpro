<?php

namespace App\Models\JPLimpieza;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'logs';
    protected $fillable = ['level', 'message', 'context', 'entidad', 'entidad_id', 'accion', 'valores_antes', 'valores_despues', 'user_id', 'ip'];

    protected $casts = [
        'context' => 'array',
        'valores_antes' => 'array',
        'valores_despues' => 'array',
    ];

    /**
     * Actor del registro. Sin FK a proposito: el visor debe tolerar
     * un usuario eliminado (la relacion devuelve null). El usuario vive en
     * la base principal; la relacion se resuelve con una consulta aparte,
     * sin join entre bases.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
