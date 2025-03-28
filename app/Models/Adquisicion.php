<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adquisicion extends Model
{
    use HasFactory;
    protected $table = 'adquisiciones';
    protected $fillable = [
        'fecha',
        'numero',
        'proyecto_id',
        'etapa_id',
        'tipo_etapa_id',
        'usuario_id',
        'estado',
        'tipo_adquisicion',
        'factura',
        'archivo',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function etapa()
    {
        return $this->belongsTo(CatalogoDato::class, 'etapa_id');
    }

    public function tipo_etapa()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_etapa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function adquisiciones_detalle()
    {
        return $this->hasMany(AdquisicionDetalle::class);
    }

    public function orden_recepcion()
    {
        return $this->hasOne(OrdenRecepcion::class, 'adquisicion_id');
    }


    public static function dataReporteAdquisiciones($request)
    {
        $fechas = $request->input('fecha');
        $estado = $request->input('estado');
        $proyecto = $request->input('proyecto');
        $etapa = $request->input('etapa');
        $tipo = $request->input('tipo');
        $necesidad = $request->input('necesidad');
        $costo = $request->input('costo');
        $proveedor = $request->input('proveedor');
        $producto = $request->input('producto');

        $query = self::query();

        if ($request->filled('proyecto_id')) {
            $query->where('proyecto_id', $request->input('proyecto_id'));
        }

        if ($request->filled('etapa_id')) {
            $query->where('etapa_id', $request->input('etapa_id'));
        }

        if ($request->filled('tipo_adquisicion')) {
            $query->where('tipo_adquisicion', $request->input('tipo_adquisicion'));
        }

        $query->when($estado, function ($q, $estado) {
            if ($estado == 'pendientes') {
                $q->whereIn('estado', ['En Proceso', 'Finalizado']);
            } else {
                $q->where('estado', 'Completado');
            }
        });

        // Filtrar por proyecto
        $query->when($proyecto, function ($q, $proyecto) {
            $q->where('proyecto_id', $proyecto);
        });

        // Filtrar por etapa
        $query->when($etapa, function ($q, $etapa) {
            $q->where('etapa_id', $etapa);
        });

        // Filtrar por tipo_etapa
        $query->when($tipo, function ($q, $tipo) {
            $q->where('tipo_etapa_id', $tipo);
        });

        // Filtrar por costo (INDIRECTOS O DIRECTOS)
        $query->when($costo, function ($q, $costo) {
            $q->where('etapa_id', $costo);
        });

        $query->when($necesidad, function ($q, $necesidad) {
            $q->whereHas('adquisiciones_detalle', function ($query) use ($necesidad) {
                $query->where('necesidad', $necesidad);
            })->with(['adquisiciones_detalle' => function ($query) use ($necesidad) {
                $query->select('adquisicion_id', 'cantidad_solicitada', 'articulo_id')
                    ->where('necesidad', $necesidad); // Filtrar por el producto específico
            }]);
        });

        // Filtrar por producto y obtener la cantidad
        $query->when($producto, function ($q, $producto) {
            $q->whereHas('adquisiciones_detalle', function ($query) use ($producto) {
                $query->where('articulo_id', $producto);
            })->with(['adquisiciones_detalle' => function ($query) use ($producto) {
                $query->select('adquisicion_id', 'cantidad_solicitada', 'articulo_id')
                    ->where('articulo_id', $producto); // Filtrar por el producto específico
            }]);
        });

        // Filtrar por proveedor
        $query->when($proveedor, function ($q, $proveedor) {
            $q->whereHas('orden_recepcion', function ($query) use ($proveedor) {
                $query->where('proveedor_id', $proveedor);
            });
        });

        // Filtrar por rango de fechas
        $query->when($fechas, function ($q) use ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('d/m/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('d/m/Y', trim($fechaFin))->format('Y-m-d');
            $q->whereBetween('fecha', [$fechaInicioFormatted, $fechaFinFormatted]);
        });

        return $query->get()->map(function ($adquisicion) use ($producto) {
            // Extraer solo la cantidad del producto específico
            $cantidad = $adquisicion->adquisiciones_detalle->firstWhere('articulo_id', $producto)->cantidad_solicitada ?? 0;
            return [
                'adquisicion_id' => $adquisicion,
                'cantidad' => $cantidad,
            ];
        });
    }
}