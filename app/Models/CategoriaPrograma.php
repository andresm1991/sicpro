<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaPrograma extends Model
{
    use HasFactory;
    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'categorias_programa';

    /**
     * Indica si el modelo debe tener timestamps (created_at, updated_at).
     * Como es una tabla de catálogo, generalmente no se necesitan.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'orden',
        'es_exterior',
    ];

    /**
     * Obtiene todos los espacios que pertenecen a esta categoría.
     */
    public function espacios()
    {
        return $this->hasMany(EspacioPrograma::class, 'categoria_id');
    }
}