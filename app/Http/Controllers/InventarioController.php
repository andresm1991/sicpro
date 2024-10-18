<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Inventario;
use App\Models\CatalogoDato;
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
        $productos = Articulo::where('activo', true)->pluck('descripcion', 'id');
        $estados = CatalogoDato::getChildrenCatalogo('estados.inventario')->pluck('descripcion', 'id');

        $estados = $estados->prepend('', '');
        $productos = $productos->prepend('', '');

        return view('inventario.index', compact('list_inventario', 'productos', 'estados', 'title_page', 'breadcrumbs'));
    }

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $text = $request->buscar;
            $output = "";

            $list_inventario = Inventario::obtenerStock($text);

            foreach ($list_inventario as $index => $inventario) {
                $movimientos = "<a href='javascript:void(0);' class='dropdown-item'>Movimiento</a>";
                $editar = "<a href='javascript:void(0);' class='dropdown-item'>Editar</a>";
                $eliminar = "<a href='javascript:void(0);' class='dropdown-item eliminar-inventario' id='" . $inventario->id . "'>Eliminar</a>";

                $output .= ' <tr id="' . $index . '">' .
                    '<td class="align-middle text-uppercase">' . $inventario->producto->descripcion . '</td>' .
                    '<td class="align-middle">' . $inventario->total_entradas . '</td>' .
                    '<td class="align-middle">' . $inventario->total_salidas . '</td>' .
                    '<td class="align-middle">' . $inventario->stock . '</td>' .
                    '<td class="align-middle">' . $inventario->estado->descripcion . '</td>' .
                    '<td class="align-middle text-right text-truncate p-2">' .
                    '<button type="button" class="btn btn-outline-dark" data-container="body"
                            data-toggle="popover" data-placement="left" data-trigger="focus"
                            data-content ="' . $movimientos . $editar . $eliminar . '">
                            <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                        </button>' .
                    '</td>' .
                    '</tr>';
            }

            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="6" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }
            return Response($output);
        }
    }
}
