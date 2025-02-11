<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use App\Models\CategoriaPresupuesto;
use Illuminate\Http\Request;

class RubroController extends Controller
{
    public function index()
    {
        $title_page = 'Rubros Construcción';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $etapas_construccion = CatalogoDato::getChildrenCatalogo('etapas.construccion');
        // Obtener todas las categorías con sus rubros relacionados
        $categorias = CategoriaPresupuesto::with('rubrosPresupuesto')->get();
        return view('rubros_construccion.index', compact('title_page', 'breadcrumbs', 'categorias', 'etapas_construccion'));
    }
}