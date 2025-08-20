<?php

namespace App\Exports;

use App\Models\CatalogoDato;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportAdquisicionesExport implements FromView
{

    protected $query;
    protected $tipo;
    protected $producto;
    protected $proveedor;
    protected $fechas;
    protected $cargo;
    protected $totalGeneral;
    protected $totalPagado;
    protected $totalSaldos;
    protected $view;

    public function __construct($query, $tipo, $producto, $proveedor, $fechas, $cargo, $totalGeneral, $totalPagado, $totalSaldos, $view)
    {
        $this->query = $query;
        $this->tipo = $tipo;
        $this->producto = $producto;
        $this->proveedor = $proveedor;
        $this->fechas = $fechas;
        $this->cargo = $cargo;
        $this->totalGeneral = $totalGeneral;
        $this->totalPagado = $totalPagado;
        $this->totalSaldos = $totalSaldos;
        $this->view = $view;
    }

    public function view(): View
    {
        return view('exports.' . $this->view, [
            'query' => $this->query,
            'tipo' => $this->tipo,
            'producto' => $this->producto,
            'proveedor' => $this->proveedor,
            'fechas' => $this->fechas,
            'cargo' => $this->cargo,
            'totalGeneral' => $this->totalGeneral,
            'totalPagado' => $this->totalPagado,
            'totalSaldos' => $this->totalSaldos,
        ]);
    }
}
