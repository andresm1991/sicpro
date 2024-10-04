<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index (){
        $title_page = 'Configuraciones';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Configuraciones', 'url' => '']
        ];

        $categories = CatalogoDato::where('padre_id', '=', null)->get();

        $allCategories = CatalogoDato::pluck('descripcion','id')->all();

        return view('configuracion.index', compact('categories','allCategories', 'title_page', 'breadcrumbs'));
    }
}
