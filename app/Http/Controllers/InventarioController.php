<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        $title_page = 'Inventario';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Inventario', 'url' => '']
        ];

        $list_inventario = Inventario::obtenerStock();

        return view('inventario.index', compact('list_inventario', 'title_page', 'breadcrumbs'));
    }
}