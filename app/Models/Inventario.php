<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventario extends Model
{
    use HasFactory;
    protected $table = 'inventario';
    protected $fillable = ['orden_recepcion_id', 'producto_id', 'tipo', 'cantidad', 'fecha', 'usuario_id', 'estado_id'];

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'producto_id');
    }

    public function orden_recepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id', 'id');
    }

    public static function obtenerStock($filtro_busqueda = null)
    {
        return self::select('producto_id', 'estado_id', DB::raw("
                SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE 0 END) as total_entradas,
                SUM(CASE WHEN tipo = 'salida' THEN cantidad ELSE 0 END) as total_salidas
            "))
            ->with(['producto', 'estado']) // Asegurarse de cargar la relación con Producto y Estado
            ->when($filtro_busqueda, function ($query, $filtro_busqueda) {
                // Buscar en las relaciones 'producto' o 'estado'
                $query->whereHas('producto', function ($q) use ($filtro_busqueda) {
                    $q->where('descripcion', 'LIKE', '%' . $filtro_busqueda . '%');
                })->orWhereHas('estado', function ($q) use ($filtro_busqueda) {
                    $q->where('descripcion', 'LIKE', '%' . $filtro_busqueda . '%');
                });
            })
            ->groupBy('producto_id', 'estado_id') // Agrupar también por estado_id
            ->paginate(15)
            ->map(function ($producto) {
                // Calcular el stock y agregarlo al objeto
                $producto->stock = $producto->total_entradas - $producto->total_salidas;
                return $producto;
            });
    }
}
