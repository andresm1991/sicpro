<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contratista extends Model
{
    use HasFactory;
    protected $table = 'contratistas';
    protected $fillable = ['fecha', 'plazo_semanas', 'proveedor_id', 'articulo_id', 'proyecto_id', 'etapa_id', 'tipo_etapa_id', 'usuario_id', 'estado_id', 'numero_casas'];

    public function detalle_contratistas()
    {
        return $this->hasMany(DetalleContratista::class);
    }

    public function pagosOrdenTrabajoContratista()
    {
        return $this->hasMany(PagoOrdenTrabajoContratista::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

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

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }
    public function getTotalContratistasAttribute()
    {
        $nro_casas = $this->numero_casas;
        return $this->detalle_contratistas()->selectRaw('SUM(cantidad * valor_unitario) as total')->pluck('total')->first() * $nro_casas;
    }

    public function getPagosContratistasAttribute()
    {
        return $this->pagosOrdenTrabajoContratista()->selectRaw('SUM(valor) as pagos')->pluck('pagos')->first();
    }

    public function getNumeroPagoContratistaAttribute()
    {
        return $this->pagosOrdenTrabajoContratista()
            ->selectRaw('id')
            ->latest('id')
            ->first();
    }

    public function getTipoPagoContratistaAttribute()
    {
        return $this->pagosOrdenTrabajoContratista()
            ->selectRaw('tipo_pago')
            ->latest('id')
            ->pluck('tipo_pago')
            ->first();
    }

    public static function filtroContratista($request)
    {
        $ordenado = $request->input('ordenado');
        $fechas = $request->input('fechas');
        $estado = $request->input('estado');
        $proyecto = $request->input('proyecto');
        $etapa = $request->input('etapa');
        $tipo = $request->input('tipo');
        $necesidad = $request->input('necesidad');
        $costo = $request->input('costo');
        $proveedor = $request->input('proveedor');
        $producto = $request->input('producto');
        $tipo_reporte = $request->input('tipo_reporte');

        $query = self::with(['proveedor', 'articulo', 'proyecto', 'etapa', 'tipo_etapa', 'usuario', 'estado']);
        $query->when($proveedor, function ($query) use ($proveedor) {
            $query->where('proveedor_id', $proveedor);
        });
        $query->when($proyecto, function ($query) use ($proyecto) {
            $query->where('proyecto_id', $proyecto);
        });
        $query->when($etapa, function ($query) use ($etapa) {
            $query->where('etapa_id', $etapa);
        });
        $query->when($tipo, function ($query) use ($tipo) {
            $query->where('tipo_etapa_id', $tipo);
        });

        $query->when($fechas, function ($q) use ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d');
            $q->whereBetween('fecha', [$fechaInicioFormatted, $fechaFinFormatted]);
        });
        $query->when($estado, function ($q, $estado) {
            $q->whereHas('estado', function ($query)  use ($estado) {
                $query->where('slug', $estado == 'pendientes' ? 'estados.contratistas.proceso' : 'estados.contratistas.completado');
            });
        });

        // Ordenar por secuencial u otro criterio
        switch ($ordenado) {
            case 'secuencial':
                $query->orderBy('id', 'asc');
                break;
            case 'fecha':
                $query->orderBy('fecha', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('proveedor.razon_social', 'asc');
                break;
            default:
                # code...
                break;
        }

        return $query->get()->map(function ($contratista) {
            // Calcular el total para todos los detalles
            $total = $contratista->detalle_contratistas->sum(function ($detalle) use ($contratista) {
                $cantidad = $detalle->cantidad ?? 0;
                $valor = $detalle->valor_unitario ?? 0;

                return $cantidad * $valor * $contratista->numero_casas;
            });

            return [
                'contratista' => $contratista,
                'total' => '$ ' . number_format($total, 4),
            ];
        });
    }
}
