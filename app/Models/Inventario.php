<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventario extends Model
{
    use HasFactory;
    protected $table = 'inventario';
    protected $fillable = ['orden_recepcion_id', 'producto_id', 'cantidad', 'cantidad_debaja', 'fecha', 'usuario_id', 'estado'];

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'producto_id');
    }

    public function orden_recepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }

    public static function obtenerStock($filtro_busqueda = null)
    {
        return self::select('producto_id', DB::raw("
        SUM(cantidad) as total_cantidad,
        SUM(cantidad_debaja) as total_cantidad_debaja,
        ROUND(AVG(estado)) as estado
    "))
            ->with(['producto']) // Asegurarse de cargar la relación con Producto
            ->when($filtro_busqueda, function ($query, $filtro_busqueda) {
                // Buscar en las relaciones 'producto'
                $query->whereHas('producto', function ($q) use ($filtro_busqueda) {
                    $q->where('descripcion', 'LIKE', '%' . $filtro_busqueda . '%');
                });
            })
            ->groupBy('producto_id') // Agrupar solo por producto_id
            ->paginate(15)
            ->through(function ($producto) {
                // Calcular el stock basado en cantidad y cantidad_debaja
                $producto->stock = $producto->total_cantidad - $producto->total_cantidad_debaja;
                return $producto;
            });
    }
}