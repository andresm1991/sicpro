<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;

class SistemaController extends Controller
{
    public function index()
    {
        $title_page = 'Sistema';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];
        return view('sistema.index', compact('title_page', 'breadcrumbs'));
    }

    public function proveedores()
    {
        $menus = CatalogoDato::where('padre_id', 1)->get();
        $title_page = 'Proveedores';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('proveedores.menu', compact('menus', 'title_page', 'breadcrumbs'));
    }
}
