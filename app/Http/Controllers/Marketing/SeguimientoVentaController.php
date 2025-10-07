<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Marketing\ClienteVenta;
use App\Models\Marketing\ProcesoVenta;

class SeguimientoVentaController extends Controller
{
    public function index()
    {
        $title_page = 'Seguimiento de Ventas';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Marketing', 'url' => route('marketing.index')],
            ['name' => 'Seguimiento de Ventas', 'url' => '']
        ];
        $seguimientos = ProcesoVenta::with('cliente')->latest()->paginate(15);
        return view('marketing.seguimiento_ventas.index', compact('title_page', 'breadcrumbs', 'seguimientos'));
    }

    public function create()
    {
        $title_page = 'Crear Seguimiento de Venta';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Seguimiento de Ventas', 'url' => route('marketing.seguimiento.ventas.index')],
            ['name' => 'Crear', 'url' => '']
        ];

        $clienteVenta = new ClienteVenta();
        $cliente = $clienteVenta;

        // AQUÍ ESTÁ LA MAGIA: Renderizamos la plantilla de Blade a una cadena de HTML
        $plantillaContenido = view('templates.contratos.contrato_reserva', compact('cliente'))->render();

        return view('marketing.seguimiento_ventas.create', compact('title_page', 'breadcrumbs', 'clienteVenta', 'plantillaContenido'));
    }

    public function store(Request $request)
    {
        return $request->all();
        $request->validate([
            'nombre' => 'required|string|max:255',
            'documento' => 'required|string|max:100|unique:clientes_ventas,documento',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'ciudad' => 'nullable|string|max:100',
            'valor_reserva' => 'nullable|numeric|min:0',
            'file_contrato_firmado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_cedulas.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_comprobante_reserva' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Crear el cliente de venta
            $clienteVenta = ClienteVenta::create($request->only('nombre', 'documento', 'direccion', 'telefono', 'email', 'ciudad'));

            // Crear el proceso de venta asociado al cliente
            $procesoVentaData = [
                'client_id' => $clienteVenta->id,
                'etapa_actual' => 'Reserva',
                'valor_reserva' => $request->input('valor_reserva', 0),
            ];

            // Manejar las subidas de archivos
            if ($request->hasFile('file_contrato_firmado')) {
                $path = $request->file('file_contrato_firmado')->store('contratos_firmados', 'public');
                $procesoVentaData['contrato_firmado_path'] = $path;
            }

            if ($request->hasFile('file_cedulas')) {
                $cedulasPaths = [];
                foreach ($request->file('file_cedulas') as $cedula) {
                    $cedulasPaths[] = $cedula->store('cedulas_clientes', 'public');
                }
                $procesoVentaData['cedulas_path'] = $cedulasPaths;
            }

            if ($request->hasFile('file_comprobante_reserva')) {
                $path = $request->file('file_comprobante_reserva')->store('comprobantes_reserva', 'public');
                $clienteVenta->archivo_comprobante_reserva = $path;
                $clienteVenta->save();
            }

            // Crear el proceso de venta
            ProcesoVenta::create($procesoVentaData);

            DB::commit();

            return redirect()->route('marketing.seguimiento.ventas.index')->with('success', 'Seguimiento de venta creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Ocurrió un error al crear el seguimiento de venta: ' . $e->getMessage()]);
        }
    }
}
