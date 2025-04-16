<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportSolicitudesExport implements FromView
{
    protected $query;
    protected $fechas;
    protected $tipo_solicitud;

    public function __construct($query, $fechas, $tipo_solicitud)
    {
        $this->query = $query;
        $this->fechas = $fechas;
        $this->tipo_solicitud = $tipo_solicitud;
    }

    public function view(): View
    {
        return view('exports.reporte_solicitudes', [
            'query' => $this->query,
            'fechas' => $this->fechas,
            'tipo_solicitud' => $this->tipo_solicitud,
        ]);
    }
}
