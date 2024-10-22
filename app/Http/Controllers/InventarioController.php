<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Inventario;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

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

        $productos = $productos->prepend('', '');

        return view('inventario.index', compact('list_inventario', 'productos', 'title_page', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $producto = $request->producto;
                $cantidad = $request->cantidad;
                $estado = $request->estado;

                $nuevo = ['producto_id' => $producto, 'cantidad' => $cantidad, 'estado' => $estado, 'fecha' => date('Y-m-d'), 'usuario_id' => auth()->id()];

                if (Inventario::create($nuevo)) {
                    DB::commit();
                    $inventario = Inventario::obtenerStock();
                    $response = $this->htmlTable($inventario);

                    return response()->json(['success' => true, 'mensaje' => 'Datos guardado correctamente.', 'inventario' => $response]);
                } else {
                    throw new Exception('Error a intentar guardar nuevos datos de inventario');
                }
            } catch (Throwable $e) {
                DB::rollBack();
                LogService::log('error', 'Error en inventario metodo: store', ['user_id' => auth()->id(), 'action' => 'store', 'message' => $e->getMessage()]);
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $text = $request->buscar;
            $list_inventario = Inventario::obtenerStock($text);

            $output = $this->htmlTable($list_inventario);

            return Response($output);
        }
    }

    private function htmlTable($list_inventario)
    {
        $output = '';
        foreach ($list_inventario as $index => $inventario) {
            $clase = ($inventario->estado <= 3) ? "bg-danger" : (($inventario->estado <= 7) ? "bg-warning" : "bg-success");

            $estado = '<div class="progress">
                            <div class="progress-bar ' . $clase . ' "
                                role="progressbar" style="width: ' . ($inventario->estado / 10) * 100 . '%;"
                                aria-valuenow="' . $inventario->estado . '" aria-valuemin="0"
                                aria-valuemax="10">
                                ' . $inventario->estado . '/10
                            </div>
                        </div>';
            $movimientos = "<a href='javascript:void(0);' class='dropdown-item'>Movimiento</a>";
            $eliminar = "<a href='javascript:void(0);' class='dropdown-item eliminar-inventario' id='" . $inventario->id . "'>Eliminar</a>";

            $output .= ' <tr id="' . $index . '">' .
                '<td class="align-middle text-uppercase">' . $inventario->producto->descripcion . '</td>' .
                '<td class="align-middle">' . $inventario->total_cantidad . '</td>' .
                '<td class="align-middle">' . $inventario->total_cantidad_debaja . '</td>' .
                '<td class="align-middle">' . $inventario->stock . '</td>' .
                '<td class="align-middle">' . $estado . '</td>' .
                '<td class="align-middle text-right text-truncate p-2">' .
                '<button type="button" class="btn btn-outline-dark" data-container="body"
                        data-toggle="popover" data-placement="left" data-trigger="focus"
                        data-content ="' . $movimientos . $eliminar . '">
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

        return $output;
    }
}
