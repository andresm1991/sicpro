<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;

class SistemaController extends Controller
{
    public function index()
    {
        $title_page = 'Sistema';
        return view('sistema.index', compact('title_page'));
    }

    public function proveedores()
    {
        $menus = CatalogoDato::where('padre_id', 1)->get();
        $title_page = 'Sistema / Proveedores';
        $back_route = route('sistema.index');

        return view('proveedores.menu', compact('menus', 'title_page', 'back_route'));
    }
}
