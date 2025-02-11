<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    public function index () {
        $title_page = 'Solicitudes';
        $solicitudes = Solicitud::orderBy('fecha_solicitud', 'asc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.index', compact('title_page', 'breadcrumbs', 'solicitudes'));
    }

    public function create () {
        $title_page = 'Nueva Solicitud';
        
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Solicitudes', 'url' => route('solicitud.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $solicitud = new Solicitud();
        $users = User::where('activo', true)
        ->where('id', '>', 1)->pluck('nombre', 'id');
        $tipo_solicitudes = CatalogoDato::getChildrenCatalogo('tipo.solicitudes')->pluck('descripcion', 'id');
        $estados_solicitud = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');

        return view('solicitudes.create', compact('title_page', 'breadcrumbs', 'solicitud', 'users', 'tipo_solicitudes', 'estados_solicitud'));
    }
}
