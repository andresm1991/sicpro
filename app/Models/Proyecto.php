<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proyecto extends Model
{
    use HasFactory;
    protected $table = 'proyectos';
    protected $fillable = [
        'catalogo_proyecto_id',
        'nombre_proyecto',
        'nombre_propietario',
        'ubicacion',
        'direccion',
        'telefono',
        'correo',
        'tipo_proyecto_id',
        'area_lote',
        'area_construccion',
        'numero_unidades',
        'area_lote_unidad',
        'area_construccion_unidad',
        'presupuesto_total',
        'presupuesto_unidad',
        'fecha_inicio',
        'fecha_finalizacion',
        'observacion',
        'portada',
        'costo_indirecto',
        'estado_id',
    ];

    public function tipo_proyecto()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_proyecto_id');
    }

    public function catalogo_proyecto()
    {
        return $this->belongsTo(CatalogoDato::class, 'catalogo_proyecto_id');
    }

    public function archivos_proyecto()
    {
        return $this->hasMany(ArchivoProyecto::class);
    }

    public function adquisiciones()
    {
        return $this->hasMany(Adquisicion::class);
    }

    public function presupuesto()
    {
        return $this->hasOne(PresupuestoProyecto::class);
    }

    public function cronograma_dias_semana()
    {
        return $this->hasMany(Cronograma::class);
    }

    public function mano_obra()
    {
        return $this->hasMany(ManoObra::class);
    }

    public function contratista()
    {
        return $this->hasMany(Contratista::class);
    }
    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public static function presupuestoValorado($proyectoId)
    {
        return CategoriaPresupuesto::whereHas('rubrosPresupuesto.presupuestoProyectos', function ($query) use ($proyectoId) {
            $query->where('proyecto_id', $proyectoId);
        })
            ->with(['rubrosPresupuesto' => function ($query) use ($proyectoId) {
                $query->whereHas('presupuestoProyectos', function ($q) use ($proyectoId) {
                    $q->where('proyecto_id', $proyectoId);
                })->with(['presupuestoProyectos' => function ($q) use ($proyectoId) {
                    $q->where('proyecto_id', $proyectoId);
                }]);
            }])
            ->get();
    }

    public function getSemanasTranscurridasAttribute()
    {
        $primerRegistro = $this->mano_obra('created_at', 'asc')
            ->first();

        if (!$primerRegistro) {
            return 1; // O el valor que consideres adecuado si no hay mano de obra
        }
        return semanaEnCurso($primerRegistro->fecha_inicio);

        //return ceil((Carbon::parse($primerRegistro->fecha_inicio)->diffInDays(now()) + 1) / 7);
        //return semanasTranscurridas($primerRegistro->fecha_inicio);
    }

    public function getSubproyectosUnicosAttribute()
    {
        // Obtener subproyectos únicos de cada relación
        $subproyectosProyecto = collect([$this->subproyecto])->filter()->unique();

        $subproyectosAdquisiciones = $this->adquisiciones->pluck('subproyecto')->filter()->unique();
        $subproyectosManoObra = $this->mano_obra->pluck('subproyecto')->filter()->unique();
        $subproyectosContratista = $this->contratista->pluck('subproyecto')->filter()->unique();

        // Unir y agrupar todos los subproyectos, eliminando duplicados
        $subproyectos = $subproyectosProyecto
            ->merge($subproyectosAdquisiciones)
            ->merge($subproyectosManoObra)
            ->merge($subproyectosContratista)
            ->unique()
            ->values();

        // Array asociativo para un select
        return $subproyectos->mapWithKeys(function ($item) {
            return [$item => $item];
        })->prepend('', '');
    }
}
