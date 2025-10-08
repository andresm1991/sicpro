<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\SeguimientoVenta\StoreReservaRequest;
use App\Models\Marketing\ClienteVenta;
use App\Models\Marketing\Contrato;
use App\Models\Marketing\ProcesoVenta;
use App\Services\LogService;
use Illuminate\Support\Facades\Storage;

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

        $seguimientoVenta = new ProcesoVenta();
        $cliente = $seguimientoVenta;

        // AQUÍ ESTÁ LA MAGIA: Renderizamos la plantilla de Blade a una cadena de HTML
        $plantillaContenido = view('templates.contratos.contrato_reserva', compact('cliente'))->render();

        return view('marketing.seguimiento_ventas.create', compact('title_page', 'breadcrumbs', 'seguimientoVenta', 'plantillaContenido'));
    }

    public function storeEtapaReserva(StoreReservaRequest $request)
    {
        try {
            DB::beginTransaction();
            // Crear el cliente de venta
            $clienteVenta = ClienteVenta::updateOrCreate(
                [
                    'documento' => $request->input('documento_identidad'),
                ],
                [
                    'nombre' => $request->input('nombre'),
                    'direccion'  => $request->input('direccion'),
                    'telefono' => $request->input('telefono'),
                    'email' => $request->input('email'),
                    'ciudad' => $request->input('ciudad'),
                    'activo' => true,
                    'observaciones' => $request->input('observaciones'),
                ]
            );

            // Crear el proceso de venta asociado al cliente
            $procesoVentaData = [
                'cliente_id' => $clienteVenta->id,
                'etapa_actual' => 'Reserva',
                'valor_reserva' => $request->input('valor_reserva'),
            ];

            // Manejar las subidas de archivos
            if ($request->hasFile('file_contrato_firmado')) {
                $path_archivo = $this->subriArchivo(str_replace(' ', '_', $clienteVenta->nombre), $request->file('file_contrato_firmado'), 'contrato_firmado');

                $procesoVentaData['contrato_firmado_path'] = $path_archivo;
            }

            if ($request->hasFile('fila_comprobante_pago_reserva')) {
                $path_archivo = $this->subriArchivo(str_replace(' ', '_', $clienteVenta->nombre), $request->file('fila_comprobante_pago_reserva'), 'pago_reserva');

                $procesoVentaData['comprobante_pago_reserva_path'] = $path_archivo;
            }

            if ($request->hasFile('file_cedulas')) {
                $cedulasPaths = [];
                foreach ($request->file('file_cedulas') as $cedula) {
                    $cedulasPaths[] = $this->subriArchivo(str_replace(' ', '_', $clienteVenta->nombre), $cedula, 'cedulas');
                }
                $procesoVentaData['cedulas_path'] = $cedulasPaths;
            }

            // Crear el proceso de venta
            $procesoVenta = ProcesoVenta::create($procesoVentaData);

            Contrato::create([
                'proceso_venta_id' => $procesoVenta->id,
                'titulo' => 'contrato reserva',
                'contenido' => $request->input('contenido')
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Seguimiento de venta creado exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al crear el seguimiento de venta: ' . $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al crear el seguimiento de venta']);
        }
    }

    public function verificarDocumentoIdentidad(Request $request)
    {
        $valor = $request->input('valor');
        $existe = ClienteVenta::where('documento', $valor)->first();

        if ($existe) {
            return response()->json(['success' => true, 'cliente' => $existe]);
        } else {
            return response()->json(['success' => false, 'cliente' => '']);
        }
    }

    public function editar(ProcesoVenta $seguimiento)
    {
        $title_page = 'Editar Seguimiento de Venta';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Seguimiento de Ventas', 'url' => route('marketing.seguimiento.ventas.index')],
            ['name' => 'editar', 'url' => '']
        ];

        $seguimientoVenta = $seguimiento;

        // AQUÍ ESTÁ LA MAGIA: Renderizamos la plantilla de Blade a una cadena de HTML
        $plantillaContenido = $seguimiento->contrato->contenido;


        return view('marketing.seguimiento_ventas.edit', compact('title_page', 'breadcrumbs', 'seguimientoVenta', 'plantillaContenido'));
    }

    public function actualizarEtapaReserva(Request $request, ProcesoVenta $seguimiento)
    {
        try {
            DB::beginTransaction();
            // actualizar la informacion del cliente
            $clienteVenta = ClienteVenta::find($seguimiento->cliente_id);
            $clienteVenta->documento = $request->input('documento_identidad');
            $clienteVenta->nombre = $request->input('nombre');
            $clienteVenta->direccion = $request->input('direccion');
            $clienteVenta->telefono = $request->input('telefono');
            $clienteVenta->email = $request->input('email');
            $clienteVenta->ciudad = $request->input('ciudad');
            $clienteVenta->activo = $request->input('estado', true);
            $clienteVenta->observaciones = $request->input('observaciones');
            $clienteVenta->save();

            $contrato = Contrato::where('proceso_venta_id', $seguimiento->id)->firts();
            $contrato->contenido = $request->input('contenido');
            $contrato->save();

            $seguimiento->valor_reserva = $request->input('valor_reserva');

            // Manejar las subidas de archivos
            if ($request->hasFile('file_contrato_firmado')) {
                $path_archivo = $this->subriArchivo(str_replace(' ', '_', $clienteVenta->nombre), $request->file('file_contrato_firmado'), 'contrato_firmado');

                $seguimiento->contrato_firmado_path = $path_archivo;
            }

            if ($request->hasFile('fila_comprobante_pago_reserva')) {
                $path_archivo = $this->subriArchivo(str_replace(' ', '_', $clienteVenta->nombre), $request->file('fila_comprobante_pago_reserva'), 'pago_reserva');

                $seguimiento->comprobante_pago_reserva_path = $path_archivo;
            }

            if ($request->hasFile('file_cedulas')) {
                $cedulasPaths = [];
                foreach ($request->file('file_cedulas') as $cedula) {
                    $cedulasPaths[] = $this->subriArchivo(str_replace(' ', '_', $clienteVenta->nombre), $cedula, 'cedulas');
                }
                $seguimiento->cedulas_path = $cedulasPaths;
            }

            // actualziar el proceso de venta
            $seguimiento->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Seguimiento de venta actualizado  exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al actualizar el seguimiento de venta: ' . $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar el seguimiento de venta']);
        }
    }

    private function subriArchivo($cliente, $file, $name)
    {
        $extension = $file->getClientOriginalExtension();
        $path_file = Storage::disk('digitalocean')->putFileAs('marketing/seguimiento-ventas/' . $cliente . '/' . date('Y-m-d'), $file, $name . '.' . $extension);

        return $path_file;
    }
}
