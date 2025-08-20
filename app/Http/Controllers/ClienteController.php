<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ClienteStoreRequest;

class ClienteController extends Controller
{
    public function index()
    {
        $title_page = 'Clientes';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => $title_page, 'url' => '']
        ];
        // Obtener los clientes ordenados por fecha de creación
        $clientes = Cliente::orderBy('created_at', 'desc')->paginate(15);
        return view('clientes.index', compact('title_page', 'breadcrumbs', 'clientes'));
    }

    public function create()
    {
        $title_page = 'Nuevo Cliente';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Clientes', 'url' => route('sistema.clientes.index')],
            ['name' => $title_page, 'url' => '']
        ];
        $cliente = new Cliente();
        return view('clientes.create', compact('title_page', 'breadcrumbs', 'cliente'));
    }

    public function store(ClienteStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            // Validar y crear el cliente
            $cliente = Cliente::create([
                'nombre' => $request->input('nombres'),
                'direccion' => $request->input('direccion'),
                'telefono' => $request->input('telefono'),
                'email' => $request->input('correo'),
                'ciudad' => $request->input('ciudad'),
                'activo' => $request->input('activo') ? true : false,
                'tipo_cliente' => $request->input('tipo_cliente'),
                'ruc' => $request->input('identificacion'),
                'contacto' => $request->input('contacto'),
                'telefono_contacto' => $request->input('telefono_contacto'),
                'email_contacto' => $request->input('correo_contacto'),
                'observaciones' => $request->input('observaciones')
            ]);
            if (!$cliente) {
                throw new \Exception('Error al crear el cliente.');
            }
            DB::commit();
            return redirect()->route('sistema.clientes.index')->with('success', 'Cliente creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return redirect()->back()->with('error', 'Error al crear el cliente: ' . $e->getMessage());
        }
    }

    public function edit(Cliente $cliente)
    {
        $title_page = 'Editar Cliente';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Clientes', 'url' => route('sistema.clientes.index')],
            ['name' => $title_page, 'url' => '']
        ];
        return view('clientes.edit', compact('title_page', 'breadcrumbs', 'cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'nombres' => 'required|unique:clientes,nombre,' . $cliente->id,
            ]);

            // Validar y actualizar el cliente
            $cliente->update([
                'nombre' => $request->input('nombres'),
                'direccion' => $request->input('direccion'),
                'telefono' => $request->input('telefono'),
                'email' => $request->input('correo'),
                'ciudad' => $request->input('ciudad'),
                'activo' => $request->input('activo') ? true : false,
                'tipo_cliente' => $request->input('tipo_cliente'),
                'ruc' => $request->input('identificacion'),
                'contacto' => $request->input('contacto'),
                'telefono_contacto' => $request->input('telefono_contacto'),
                'email_contacto' => $request->input('correo_contacto'),
                'observaciones' => $request->input('observaciones')
            ]);
            DB::commit();
            return redirect()->route('sistema.clientes.edit', $cliente->id)->with('success', 'Cliente actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return redirect()->back()->with('error', 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    public function destroy(Cliente $cliente)
    {
        try {
            DB::beginTransaction();
            $cliente->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el cliente: ' . $e->getMessage()]);
        }
    }

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->input('text');
            $output = "";

            $clientes = Cliente::where('nombre', 'like', '%' . $buscar . '%')
                ->orWhere('ruc', 'like', '%' . $buscar . '%')
                ->paginate(15);

            if ($clientes) {
                foreach ($clientes as $cliente) {
                    $output .= '<tr id="' . $cliente->id . '">';
                    $output .= '<td class="align-middle">' . $cliente->nombre . '</td>';
                    $output .= '<td class="align-middle">' . $cliente->ruc . '</td>';
                    $output .= '<td class="align-middle">' . $cliente->telefono . '</td>';
                    $output .= '<td class="align-middle">' . $cliente->email . '</td>';
                    $output .= '<td class="align-middle text-center"><span class="badge ' . ($cliente->activo ? 'badge-success' : 'badge-warning') . '">' . ($cliente->activo ? 'SI' : 'NO') . '</span></td>';
                    $output .= '<td class="align-middle table-actions">';
                    $output .= '<div class="action-buttons">';
                    $output .= '<a href="' . route('sistema.clientes.edit', $cliente->id) . '" class="btn btn-secondary btn-sm btn-space"><i class="fa-light fa-pen-to-square"></i> Editar</a>';
                    $output .= '<a href="javascript:void(0);" class="btn btn-danger btn-sm eliminar-cliente" id="' . $cliente->id . '"><i class="fa-solid fa-trash-can"></i> Eliminar</a>';
                    $output .= '</div></td>';
                    $output .= '</tr>';
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
}