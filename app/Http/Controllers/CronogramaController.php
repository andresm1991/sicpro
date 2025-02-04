<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;

class CronogramaController extends Controller
{
    public function index(Proyecto $proyecto) {
        $title_page = 'Cronograma';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('proyecto.view', ['tipo' => $proyecto->catalogo_proyecto->descripcion, 'tipo_id' => $proyecto->catalogo_proyecto->id, 'proyecto' => $proyecto->id])],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $categorias = $proyecto->presupuestoValorado($proyecto->id);
        $plazo_semanas = plazoSemanasProyecto($proyecto->fecha_inicio, $proyecto->fecha_fin);

        return view('cronograma.index', compact('title_page', 'breadcrumbs', 'proyecto', 'categorias', 'plazo_semanas'));
    }
}
