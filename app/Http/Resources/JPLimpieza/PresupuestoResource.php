<?php

namespace App\Http\Resources\JPLimpieza;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresupuestoResource extends JsonResource
{
    private $presupuestoAgrupado;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->presupuestoAgrupado = $this->transformarPresupuesto();
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            // Datos básicos del proyecto
            'id' => $this->id,
            'nombre_proyecto' => $this->nombre_proyecto,
            'entidad' => $this->entidad,
            'telefono' => $this->telefono,
            'metros_contratado' => $this->metros_contratado,
            'precio_por_metro' => $this->precio_por_metro,
            'tiempo_contratado' => $this->tiempo_contratado,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_finalizacion' => $this->fecha_finalizacion,
            'archivo_portada' => $this->archivo_portada,
            'archivo_orden_compra' => $this->archivo_orden_compra,
            'archivo_acta_final' => $this->archivo_acta_final,
            'observacion' => $this->observacion,
            'estado' => $this->estado,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Presupuesto organizado por categoría
            'presupuesto_agrupado' => $this->presupuestoAgrupado,

            // Totales calculados
            'totales' => $this->calcularTotales()
        ];
    }

    public function getPresupuestoAgrupado()
    {
        return $this->presupuestoAgrupado;
    }

    /**
     * Transforma la estructura del presupuesto
     */
    protected function transformarPresupuesto()
    {
        return $this->presupuesto->groupBy(function ($item) {
            return $item->rubroPresupuesto->categoriaPresupuesto->id;
        })->map(function ($presupuestos, $categoriaId) {
            // Obtenemos la primera categoría para sacar el nombre
            $categoria = $presupuestos->first()->rubroPresupuesto->categoriaPresupuesto;
            return [
                'categoria_id' => $categoriaId,
                'categoria_nombre' => $categoria->nombre,
                'rubros' => $presupuestos->map(function ($presupuesto) {
                    return [
                        'rubro' => $presupuesto->rubroPresupuesto->nombre,
                        'detalles' => [
                            'id' => $presupuesto->id,
                            'proyecto_id' => $presupuesto->proyecto_id,
                            'rubro_presupuesto_id' => $presupuesto->rubro_presupuesto_id,
                            'cantidad' => $presupuesto->cantidad,
                            'precio_unitario' => (float)$presupuesto->precio_unitario,
                            'iva' => $presupuesto->iva,
                            'meses' => $presupuesto->meses,
                            'subtotal' => $presupuesto->cantidad * $presupuesto->precio_unitario,
                            'total_sin_iva' => $presupuesto->cantidad * $presupuesto->precio_unitario * $presupuesto->meses,
                            'total_iva' => ($presupuesto->cantidad * $presupuesto->precio_unitario * $presupuesto->meses) * ($presupuesto->iva / 100),
                            'total_con_iva' => ($presupuesto->cantidad * $presupuesto->precio_unitario * $presupuesto->meses) * (1 + ($presupuesto->iva / 100)),
                            'created_at' => $presupuesto->created_at,
                            'updated_at' => $presupuesto->updated_at
                        ]
                    ];
                })
            ];
        })->values();
    }

    /**
     * Calcula los totales del presupuesto
     */
    protected function calcularTotales()
    {
        return [
            'subtotal' => $this->presupuesto->sum(function ($item) {
                return $item->cantidad * $item->precio_unitario * $item->meses;
            }),
            'total_iva' => $this->presupuesto->sum(function ($item) {
                return ($item->cantidad * $item->precio_unitario * $item->meses) * ($item->iva / 100);
            }),
            'gran_total' => $this->presupuesto->sum(function ($item) {
                return ($item->cantidad * $item->precio_unitario * $item->meses) * (1 + ($item->iva / 100));
            }),
            'total_rubros' => $this->presupuesto->count(),
            'total_categorias' => $this->presupuesto->unique(function ($item) {
                return $item->rubroPresupuesto->categoriaPresupuesto->id;
            })->count()
        ];
    }
}