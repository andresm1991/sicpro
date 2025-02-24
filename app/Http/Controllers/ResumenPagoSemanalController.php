<?php

namespace App\Http\Controllers;

use App\Models\ResumenPagoSemanal;
use Illuminate\Http\Request;

class ResumenPagoSemanalController extends Controller
{
    public function index()
    {
        $title_page = 'Agenda';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'administrativo', 'url' => route('administrativo.menu.construccion')],
            ['name' => 'resumen pagos semanales', 'url' => '']
        ];

        $resumen_pagos = ResumenPagoSemanal::paginate(15);

        return view('resumen_pagos_semanales.index', compact('title_page', 'breadcrumbs', 'resumen_pagos'));
    }
}