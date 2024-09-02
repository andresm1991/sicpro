<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\ProveedorCreateRequest;
use App\Models\Articulo;
use App\Models\Banco;
use App\Models\Proveedor;
use App\Models\ProveedorArticulo;
use App\Services\LogService;

class ProveedorController extends Controller
{
    protected $menu_id;

    function __construct() {}

    public function index($menu_id)
    {
        $menu_data = CatalogoDato::where('id', $menu_id)->first();
        $title_page = $menu_data->descripcion;
        $slug = $menu_data->slug;

        $proveedores = Proveedor::where('categoria_proveedor_id', $menu_id)->orderBy('razon_social', 'asc')->paginate(15);

        $back_route = route('sistema.proveedor.menu');
        return view('proveedores.index', compact('proveedores', 'menu_id', 'title_page', 'slug', 'back_route'));
    }

    public function create($menu_id)
    {
        $menu_data = CatalogoDato::where('id', $menu_id)->first();
        $articulos = Articulo::where('activo', 1)
            ->where('categoria_id', $menu_id != 2 ? 19 : 18)
            ->pluck('descripcion', 'id');
        $bancos = Banco::where('activo', true)->pluck('descripcion', 'id');
        $tipo_cuenta = CatalogoDato::getChildrenCatalogo('tipo.cuentas')->pluck('descripcion', 'id');
        $title_page = $menu_data->descripcion;
        $slug = $menu_data->slug;


        $proveedor = new Proveedor();
        $back_route = encrypted_route('sistema.proveedor.index', ['menu_id' => $menu_id]);

        return view('proveedores.create', compact('proveedor', 'menu_id', 'title_page', 'slug', 'back_route', 'bancos', 'tipo_cuenta', 'articulos'));
    }

    public function store(ProveedorCreateRequest $request, $menu_id)
    {
        $telefonos = $request->has('telefono') ? implode(',', $request->get('telefono')) : null;
        $calificacion = $request->has('calificacion') ? $request->get('calificacion') : null;

        DB::beginTransaction();
        try {
            $parametros = [
                'categoria_proveedor_id' => $menu_id,
                'documento' => $request->get('documento'),
                'razon_social' => $request->get('nombres'),
                'telefono' => $telefonos,
                'correo' => $request->get('email'),
                'direccion' => $request->get('direccion'),
                'banco_id' => $request->get('banco'),
                'tipo_cuenta_id' => $request->get('tipo_cuenta'),
                'numero_cuenta' => $request->get('numero_cuenta'),
                'observacion' => $request->get('observacion'),
                'calificacion' => $calificacion,
            ];

            if ($proveedor = Proveedor::create($parametros)) {
                if ($request->get('articulos')) {
                    $articulos = $request->get('articulos');
                    foreach ($articulos as $key => $articulo) {
                        ProveedorArticulo::create([
                            'proveedor_id' => $proveedor->id,
                            'articulo_id' => $articulo,
                        ]);
                    }
                }
                DB::commit();
                LogService::log('info', 'Proveedor creado con éxito', ['user_id' => auth()->id(), 'action' => 'create']);
                return redirect()->route('sistema.proveedor.create', Crypt::encrypt($menu_id))->with('success', 'La información ingresada se ha guardado con éxito.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear Proveedor', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->route('sistema.proveedor.create', Crypt::encrypt($menu_id))->with('error', 'Error al intentar guardar la información.');
        }
    }

    public function edit($menu_id, Proveedor $proveedor)
    {
        $menu_data = CatalogoDato::where('id', $menu_id)->first();
        $bancos = Banco::where('activo', true)->pluck('descripcion', 'id');
        $tipo_cuenta = CatalogoDato::getChildrenCatalogo('tipo.cuentas')->pluck('descripcion', 'id');
        $title_page = $menu_data->descripcion;
        $slug = $menu_data->slug;
        $articulos = Articulo::where('activo', 1)
            ->where('categoria_id', $menu_id != 2 ? 19 : 18)
            ->pluck('descripcion', 'id');

        $back_route = encrypted_route('sistema.proveedor.index', ['menu_id' => $menu_id]);

        return view('proveedores.edit', compact('proveedor', 'menu_id', 'title_page', 'slug', 'back_route', 'bancos', 'tipo_cuenta', 'articulos'));
    }

    public function update(Request $request, $menu_id, Proveedor $proveedor)
    {
        $telefonos =  implode(',', $request->get('telefono'));
        $calificacion = $request->has('calificacion') ? $request->get('calificacion') : null;
        $old_proveedor = Proveedor::find($proveedor->id);

        DB::beginTransaction();
        try {
            $proveedor->documento = $request->get('documento');
            $proveedor->razon_social = $request->get('nombres');
            $proveedor->correo = $request->get('email');
            $proveedor->direccion = $request->get('direccion');
            $proveedor->banco_id = $request->get('banco');
            $proveedor->tipo_cuenta_id = $request->get('tipo_cuenta');
            $proveedor->numero_cuenta = $request->get('numero_cuenta');
            $proveedor->observacion = $request->get('observacion');
            $proveedor->calificacion = $calificacion;
            if (is_array($request->get('telefono')) && !empty(array_filter($request->get('telefono'), function ($item) {
                return !is_null($item);
            }))) {
                $proveedor->telefono = $telefonos;
            }

            if ($proveedor->save()) {
                DB::commit();
                LogService::log('info', 'Proveedor actualizado con éxito.', ['user_id' => auth()->id(), 'action' => 'update', 'old_data' => $old_proveedor, 'new_data' => $proveedor]);
                return redirect()->route('sistema.proveedor.edit', ['menu_id' => $menu_id, 'proveedor' => $proveedor->id])->with('success', 'La información ingresada se ha guardado con éxito.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('info', 'Error al actualizar Proveedor', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('sistema.proveedor.edit', ['menu_id' => $menu_id, 'proveedor' => $proveedor->id])->with('error', 'Error al intentar guardar la información.');
        }
    }


    public function delete($menu_id, $proveedor)
    {
        $proveedor = Proveedor::find($proveedor);
        $is_delete = $proveedor->delete();
        if ($is_delete) {
            LogService::log('info', 'Proveedor eliminado con éxito.', ['user_id' => auth()->id(), 'action' => 'delete', 'data' => $proveedor]);
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
        } else {
            LogService::log('info', 'Error al eliminar Proveedor', ['user_id' => auth()->id(), 'action' => 'delete']);
            return response()->json(['success' => false, 'message' => 'Error al intentar eliminar el registro.']);
        }
    }

    public function buscar(Request $request, $menu_id)
    {
        if ($request->ajax()) {
            $buscar = $request->text;
            $output = "";

            $proveedores = Proveedor::where('categoria_proveedor_id', $menu_id)
                ->where(function ($query) use ($buscar) {
                    $query->where('documento', 'LIKE', '%' . $buscar . "%")
                        ->orWhere('razon_social', 'LIKE', '%' . $buscar . "%")
                        ->orWhere('correo', 'LIKE', '%' . $buscar . "%");
                })
                ->orderBy('razon_social', 'asc')
                ->paginate(15);

            foreach ($proveedores as $proveedor) {
                $telefonos = '';
                foreach (explode_param($proveedor->telefono) as $telefono) {
                    $telefonos .=  '<td class="align-middle">' . $telefono . '</td>';
                }

                $output .= '<tr id="' . $proveedor->id . '">' .
                    '<td class="align-middle">' . $proveedor->documento . '</td>' .
                    '<td class="align-middle text-capitalize">' . $proveedor->razon_social . '</td>' .
                    $telefonos .
                    '<td class="align-middle">' . $proveedor->correo . '</td>' .
                    '<td class="align-middle table-actions">' .
                    '<div class="action-buttons">' .
                    '<a href="' . route('sistema.proveedor.edit', ['menu_id' => Crypt::encrypt($menu_id), 'proveedor' => $proveedor->id]) . '" class="btn btn-secondary btn-sm btn-space"><i class="fa-light fa-pen-to-square"></i> Editar</a>' .
                    '<a href="javascript:void(0);" class="btn btn-danger btn-sm delete-proveedor" id="' . $proveedor->id . '"><i class="fa-solid fa-trash-can"></i> Eliminar</a>' .
                    '</div>' .
                    '</td>' .
                    '</tr>';
            }

            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="7" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }

            return Response($output);
        }
    }
}
