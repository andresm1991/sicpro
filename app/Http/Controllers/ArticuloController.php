<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ArticuloStoreRequest;
use App\Http\Requests\ArticuloUpdateRequest;

class ArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title_page = 'Productos';
        $back_route = route('sistema.index');
        $categorias = CatalogoDato::getChildrenCatalogo('tipo.adquisiciones');
        $articulos = Articulo::orderBy('categoria_id', 'asc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Productos', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];


        return view('articulos.index', compact('articulos', 'categorias', 'title_page', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ArticuloStoreRequest $request)
    {
        if ($request->ajax()) {
            $param = [
                'categoria_id' => $request->get('categoria'),
                'codigo' => $request->get('codigo'),
                'descripcion' => $request->get('descripcion'),
                'activo' => $request->get('status'),
            ];
            DB::beginTransaction();
            if (Articulo::create($param)) {
                DB::commit();
                $articulos = Articulo::orderBy('categoria_id', 'asc')->paginate(15);
                $response = $this->htmlTable($articulos);
                return response()->json(['success' => true, 'mensaje' => 'Datos guardado correctamente.', 'articulos' => $response]);
            } else {
                throw 'Error al intentar guardar la información.';
            }
            try {
            } catch (Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ArticuloUpdateRequest $request, Articulo $articulo)
    {
        if ($request->ajax()) {
            $articulo->categoria_id = $request->get('categoria');
            $articulo->codigo = $request->get('codigo');
            $articulo->descripcion = $request->get('descripcion');
            $articulo->activo = $request->get('status');

            DB::beginTransaction();
            if ($articulo->save()) {
                DB::commit();
                $articulos = Articulo::orderBy('categoria_id', 'asc')->paginate(15);
                $response = $this->htmlTable($articulos);
                return response()->json(['success' => true, 'mensaje' => 'Datos guardado correctamente.', 'articulos' => $response]);
            } else {
                throw 'Error al intentar guardar la información.';
            }
            try {
            } catch (Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $is_delete = Articulo::find($id)->delete();
        if ($is_delete) {
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente']);
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el registro']);
        }
    }

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;

            $articulos = Articulo::where('descripcion', 'LIKE', '%' . $buscar . "%")
                ->orWhere('codigo', 'LIKE', '%' . $buscar . "%")
                ->orWhereHas('categoria_articulo', function ($query) use ($buscar) {
                    return $query->where('descripcion', 'LIKE', $buscar . '%');
                })
                ->orderBy('categoria_id', 'asc')
                ->get();

            $output = $this->htmlTable($articulos);
            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="5" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }
            return Response($output);
        }
    }

    private function htmlTable($data, $request = null)
    {
        foreach ($data as $key => $element) {
            $estado = $element->activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';

            $request .= ' <tr id="' . $element->id . '">' .
                '<td class="align-middle">' . $element->categoria_articulo->descripcion . '</td>' .
                '<td class="align-middle text-uppercase">' . $element->codigo . '</td>' .
                '<td class="align-middle text-capitalize">' . $element->descripcion . '</td>' .
                '<td class="align-middle text-capitalize">' . $estado . '</td>' .
                '<td class="align-middle table-actions">' .
                '<div class="action-buttons">' .
                '<a href="javascript:void(0);" class="btn btn-secondary btn-sm btn-space editar_articulo" data-id="' . $element->id . '" data-categoria="' . $element->categoria_id . '" data-codigo="' . $element->codigo . '" data-descripcion="' . $element->descripcion . '" data-estado="' . $element->activo . '"><i class="fa-light fa-pen-to-square"></i> Editar</a>' .
                '<a href="javascript:void(0);" class="btn btn-danger btn-sm delete-articulo" id="' . $element->id . '"><i class="fa-solid fa-trash-can"></i> Eliminar</a>' .
                '</div>' .
                '</td>' .
                '</tr>';
        }

        return $request;
    }
}
