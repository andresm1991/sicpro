<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionCaja extends Model
{
    use HasFactory;

    protected $table = 'revision_cajas';

    protected $fillable = [
        'fecha_revision_inicio',
        'fecha_revision_fin',
        'usuario_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_revision_inicio' => 'date',
        'fecha_revision_fin' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getFechaRevisionInicioFormattedAttribute()
    {
        return $this->fecha_revision_inicio ? $this->fecha_revision_inicio->format('d/m/Y') : null;
    }
    public function getFechaRevisionFinFormattedAttribute()
    {
        return $this->fecha_revision_fin ? $this->fecha_revision_fin->format('d/m/Y') : null;
    }
}
