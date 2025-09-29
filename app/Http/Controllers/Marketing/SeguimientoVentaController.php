<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing\ClienteVenta;
use Illuminate\Http\Request;

class SeguimientoVentaController extends Controller
{
    public function index()
    {
        $title_page = 'Seguimiento de Ventas';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Marketing', 'url' => route('marketing.index')],
            ['name' => 'Seguimiento de Ventas', 'url' => '']
        ];
        $seguimientos = ClienteVenta::orderBy('created_at')->paginate(15);
        return view('marketing.seguimiento_ventas.index', compact('title_page', 'breadcrumbs', 'seguimientos'));
    }

    public function create()
    {
        $title_page = 'Crear Seguimiento de Venta';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Seguimiento de Ventas', 'url' => route('marketing.seguimiento.ventas.index')],
            ['name' => 'Crear', 'url' => '']
        ];
        return view('marketing.seguimiento_ventas.create', compact('title_page', 'breadcrumbs'));
    }
}
