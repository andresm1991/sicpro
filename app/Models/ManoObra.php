<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManoObra extends Model
{
    use HasFactory;
    protected $table = 'mano_obra';
    protected $fillable = [
        'semana',
        'fecha_inicio',
        'fecha_fin',
        'proyecto_id',
        'etapa_id',
        'tipo_etapa_id',
        'actividad_id',
        'usuario_id'
    ];

    public function detalle_mano_obra()
    {
        return $this->hasMany(DetalleManoObra::class);
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

    public function actividad()
    {
        return $this->belongsTo(CatalogoDato::class, 'actividad_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pago_mano_obra()
    {
        return $this->hasMany(PagoManoObra::class);
    }

    // Obtener todos los registros de mano de obra y agruparlos por proveedor y fechas
    public static function getDetalleManoObraGroupTrabajador($mano_obra_id, $estado = null)
    {
        $info_mano_obra = ['detalle' => []];

        $mano_obra_info = ManoObra::find($mano_obra_id);
        $detalles = DetalleManoObra::with(['proveedor', 'articulo'])
            ->where('mano_obra_id', $mano_obra_id)
            ->orderBy('fecha', 'asc')
            ->get();
        $agrupados = $detalles->groupBy('proveedor_id');

        foreach ($agrupados as $proveedor_id => $registros_por_proveedor) {
            $nombre_mostrado = false;  // Bandera para saber si ya mostramos el nombre del proveedor

            if ($estado == 'completo') {
                $prestamos = Prestamo::with('obtenerPagosPrestamo')
                    ->where('trabajador_id', $proveedor_id)
                    ->get();
            } else {
                $prestamos = Prestamo::with('pagos_prestamo')
                    ->where('trabajador_id', $proveedor_id)
                    ->whereHas('estado', function ($query) {
                        $query->where('descripcion', 'Pendiente');
                    })->get();
            }
            //$prestamos = Prestamo::where('trabajador_id', $proveedor_id)->get();
            foreach ($registros_por_proveedor->groupBy('articulo_id') as $articulo_id => $registros) {
                // Inicializamos las variables para cada trabajador y su cargo
                $fila = [
                    'nombre' => '',
                    'cargo' => '',
                    'dias' => array_fill(0, 6, 0),   // Días de la semana en blanco (Lunes a Sábado)
                    'total_adicional' => 0,
                    'total' => 0,
                    'total_descuento' => 0,
                    'liquido_recibir' => 0,
                    'observacion' => [],
                    'detalle_adicional' => [],
                    'detalle_descuento' => [],
                    'prestamo' => [],
                ];

                // Iteramos los registros de cada proveedor y cargo
                foreach ($registros as $detalle) {
                    $articulo = $detalle->articulo;
                    $proveedor = $detalle->proveedor;

                    $fila['nombre'] = strtoupper($proveedor->razon_social);
                    // El cargo puede cambiar por artículo
                    $fila['cargo'] = $articulo->descripcion;

                    // Convertimos la fecha a día de la semana (1 = Lunes, 2 = Martes, etc.)
                    $diaSemana = Carbon::parse($detalle->fecha)->dayOfWeek;  // 0 = Domingo, 1 = Lunes, etc.

                    // Si el día de la semana está entre Lunes y Sábado
                    if ($diaSemana >= 1 && $diaSemana <= 6) {
                        // Restamos 1 a `diaSemana` para ajustar al índice (Lunes = 0, Sábado = 5)
                        $fila['dias'][$diaSemana - 1] += $detalle->valor;
                    }

                    // Acumulamos los totales
                    $fila['total'] += $detalle->valor + $detalle->adicional;
                    $fila['total_adicional'] += $detalle->adicional;
                    $fila['total_descuento'] += $detalle->descuento;
                    if ($detalle->detalle_adicional) {
                        $fila['detalle_adicional'][] = $detalle->detalle_adicional;
                    }
                    if ($detalle->detalle_descuento) {
                        $fila['detalle_descuento'][] = $detalle->detalle_descuento;
                    }



                    // Si existe una observación, la agregamos
                    if (!empty($detalle->observacion)) {
                        $fila['observacion'][] = $detalle->observacion;  // Concatenamos las observaciones
                    }
                }

                // Calculamos el líquido a recibir
                $fila['liquido_recibir'] = ($fila['total_adicional'] + array_sum($fila['dias'])) - $fila['total_descuento'];

                // Procesar préstamos y pagos
                foreach ($prestamos as $prestamo) {
                    // Accede a los campos de cada pago
                    if ($estado != 'completo') {
                        foreach ($prestamo->pagos_prestamo as $pago) {
                            $fila['prestamo'][] = ['pago_id' => $pago->id, 'pagos' => $pago->monto_pagado];
                        }
                    } else {
                        foreach ($prestamo->obtenerPagosPrestamo as $pago) {
                            $fila['prestamo'][] = ['pago_id' => $pago->id, 'pagos' => $pago->monto_pagado];
                        }
                    }
                }



                // Añadimos la fila al array de resultados
                $info_mano_obra['detalle'][] = $fila;

                // Para las siguientes filas del mismo proveedor, dejamos el nombre en blanco
                $nombre_mostrado = true;
            }
        }

        return $info_mano_obra;
    }
}