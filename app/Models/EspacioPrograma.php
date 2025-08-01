<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspacioPrograma extends Model
{
    use HasFactory;
    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'espacios_programa';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'programa_id',
        'categoria_id',
        'espacio',
        'cantidad',
        'actividades',
        'mobiliario',
        'usuario',
        'm2',
        'observaciones',
        'link_ref',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'm2' => 'decimal:2',
        'cantidad' => 'integer',
        'usuario' => 'integer',
    ];

    /**
     * Obtiene el programa arquitectónico al que pertenece este espacio.
     */
    public function programa()
    {
        return $this->belongsTo(ProgramaArquitectonico::class, 'programa_id');
    }

    /**
     * Obtiene la categoría a la que pertenece este espacio.
     */
    public function categoria()
    {
        return $this->belongsTo(CategoriaPrograma::class, 'categoria_id');
    }
}