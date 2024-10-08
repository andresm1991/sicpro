<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $title_page = 'Configuraciones';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Configuraciones', 'url' => '']
        ];

        $categories = CatalogoDato::where('padre_id', '=', null)->get();

        $allCategories = CatalogoDato::pluck('descripcion', 'id')->all();

        return view('configuracion.index', compact('categories', 'allCategories', 'title_page', 'breadcrumbs'));
    }

    public function detalle(CatalogoDato $config)
    {
        $title_page = $config->descripcion;
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Configuraciones', 'url' => route('sistema.config.index')],
            ['name' => $title_page, 'url' => '']
        ];

        return view('configuracion.informacion_general', compact('title_page', 'breadcrumbs'));
    }
}