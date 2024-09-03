<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoProyecto extends Model
{
    use HasFactory;
    protected $table = 'archivos_proyecto';
    protected $fillable = [
        'proyecto_id',
        'nombre',
        'ruta_archivo',
        'tipo_archivo',
        'size',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
}
