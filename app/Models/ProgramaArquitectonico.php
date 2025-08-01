<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramaArquitectonico extends Model
{
    use HasFactory;
    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'programas_arquitectonicos';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'proforma_id',
        'plantilla_id',
        'nombre',
        'estilo',
        'urls_referencias',
    ];

    /**
     * Obtiene la proforma a la que pertenece el programa.
     */
    public function proforma()
    {
        return $this->belongsTo(ProformaPlano::class, 'proforma_id'); // Asegúrate de que el modelo Proforma exista
    }

    /**
     * Obtiene la plantilla de la que se originó este programa.
     */
    public function template()
    {
        return $this->belongsTo(ProgramaArquitectonico::class, 'plantilla_id');
    }

    /**
     * Obtiene todos los espacios asociados a este programa.
     */
    public function espacios()
    {
        return $this->hasMany(EspacioPrograma::class, 'programa_id');
    }
}