<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Models\Proveedor;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Contratista;
use FontLib\Table\Type\prep;

class ContratistaController extends Controller
{
    public function index(Request $request, $proyecto)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => 'Contratistas', 'url' => ''],
        ];


        $contratistas = Contratista::where('proyecto_id', $proyecto)
            ->orderBy('created_at', 'desc')->paginate(15);

        return view('jp_limpieza.contratistas.index', compact('contratistas', 'breadcrumbs', 'proyecto'));
    }

    public function create($proyecto)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('jp.limpieza.contratistas.index', $proyecto)],
            ['name' => 'Nuevo contratista', 'url' => ''],
        ];

        $contratista = new Contratista();
        $numero = numeroPedido(Contratista::first());

        $tipo_proveedor = CatalogoDato::getIdCatalogo('contratista');

        $proveedores = Proveedor::where('categoria_proveedor_id', $tipo_proveedor)->pluck('razon_social', 'id');
        $proveedores->prepend('', '');

        return view('jp_limpieza.contratistas.create', compact('contratista', 'proyecto', 'numero', 'proveedores', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        // Aquí puedes implementar la lógica para almacenar un nuevo contratista
        // Validar y guardar los datos del formulario
    }

    public function show($id)
    {
        // Aquí puedes implementar la lógica para mostrar un contratista específico
    }

    public function edit($id)
    {
        // Aquí puedes implementar la lógica para mostrar el formulario de edición de un contratista específico
    }

    public function update(Request $request, $id)
    {
        // Aquí puedes implementar la lógica para actualizar un contratista específico
    }

    public function destroy($id)
    {
        // Aquí puedes implementar la lógica para eliminar un contratista específico
    }
}
