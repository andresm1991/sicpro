<?php

namespace App\Http\Controllers\Marketing;

use Carbon\Carbon;
use App\Models\Cliente;
use Illuminate\Support\Str;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\Marketing\Contrato;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Marketing\ClienteVenta;
use App\Models\Marketing\ProcesoVenta;
use Illuminate\Support\Facades\Storage;
use App\Models\Marketing\DocumentacionItem;
use App\Models\Marketing\EscrituracionItem;
use App\Http\Requests\Marketing\SeguimientoVenta\StoreReservaRequest;

class SeguimientoVentaController extends Controller
{
    private $path_files = '';

    public function __construct()
    {
        $environment = env('APP_ENV');

        if ($environment === 'production') {
            $this->path_files = 'marketing/seguimiento-ventas/';
        } else {
            $this->path_files = 'pruebas/marketing/seguimiento-ventas/';
        }
    }

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
                'proyecto_id' => $request->input('proyecto'),
                'unidad' => $request->input('unidad'),
                'valor_venta' => $request->input('valor_venta'),
                'cliente_id' => $clienteVenta->id,
                'etapa_actual' => 'Reserva',
                'valor_reserva' => $request->input('valor_reserva'),
            ];

            $carpetaDestino = $this->path_files . 'reserva/' . date('Y-m-d') . '/' .  Str::slug($clienteVenta->nombre, '_');
            // Manejar las subidas de archivos
            if ($request->hasFile('file_contrato_firmado')) {
                $path_archivo = subirArchivo($carpetaDestino, 'contrato_firmado', $request->file('file_contrato_firmado'));

                $procesoVentaData['contrato_firmado_path'] = $path_archivo;
            }

            if ($request->hasFile('fila_comprobante_pago_reserva')) {
                $path_archivo = subirArchivo($carpetaDestino, 'pago_reserva', $request->file('fila_comprobante_pago_reserva'));

                $procesoVentaData['comprobante_pago_reserva_path'] = $path_archivo;
            }

            if ($request->hasFile('file_cedulas')) {
                $path_archivo = subirArchivo($carpetaDestino, 'cedula', $request->file('file_cedulas'));
                $procesoVentaData['cedula_path'] = $path_archivo;
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
        $plantillaContenido = $seguimiento->contrato->where('titulo', 'contrato reserva')->first()->contenido;

        if ($seguimiento->contrato->where('titulo', 'acta de entrega')->first()) {
            $plantillaActaEntrega = $seguimiento->contrato->where('titulo', 'acta de entrega')->first()->contenido;
        } else {
            $proceso = $seguimiento;
            $cliente = $seguimientoVenta->cliente;

            $fecha_entrega = Carbon::now();
            $fecha_reserva = $seguimiento->created_at;
            $plantillaActaEntrega = view('templates.contratos.acta_entrega', compact('cliente', 'proceso', 'fecha_entrega', 'fecha_reserva'))->render();
        }


        return view('marketing.seguimiento_ventas.edit', compact('title_page', 'breadcrumbs', 'seguimientoVenta', 'plantillaContenido', 'plantillaActaEntrega'));
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

            $contrato = Contrato::where('proceso_venta_id', $seguimiento->id)->first();
            $contrato->contenido = $request->input('contenido');
            $contrato->save();

            $seguimiento->valor_reserva = $request->input('valor_reserva');
            //$seguimiento->etapa_actual = $request->input('etapa_reserva_completa') ? 'Documentacion' : 'Reserva';

            $carpetaDestino = $this->path_files . 'reserva/' . date('Y-m-d') . '/' .  Str::slug($clienteVenta->nombre, '_');

            // Manejar las subidas de archivos
            if ($request->hasFile('file_contrato_firmado')) {
                $path_archivo = subirArchivo($carpetaDestino, 'contrato_firmado', $request->file('file_contrato_firmado'));

                $seguimiento->contrato_firmado_path = $path_archivo;
            }

            if ($request->hasFile('fila_comprobante_pago_reserva')) {
                $path_archivo = subirArchivo($carpetaDestino, 'pago_reserva', $request->file('fila_comprobante_pago_reserva'));

                $seguimiento->comprobante_pago_reserva_path = $path_archivo;
            }

            if ($request->hasFile('file_cedulas')) {
                $path_archivo = subirArchivo($carpetaDestino, 'cedula', $request->file('file_cedulas'));
                $seguimiento->cedulas_path = $path_archivo;
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

    public function agregarItemDocumentacion(Request $request, ProcesoVenta $proceso)
    {
        $request->validate(['nombre' => 'required|string|max:255']);

        try {
            DB::beginTransaction();
            if ($request->ajax()) {
                $item = $proceso->documentacionItems()->create($request->only('nombre'));
                DB::commit();
                $item->refresh();
                return response()->json([
                    'success' => true,
                    'message' => 'Documento agregado a la checklist.',
                    'item' => $item // Enviamos el item recién creado
                ]);
            }
            // Si no es una petición AJAX, redirigimos con un mensaje de éxito
            DB::rollBack();
            return back()->with('error', 'Error al agregar el documento.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al agregar el documento: ' . $e->getMessage()]);
            // Si la petición es AJAX, devolvemos JSON con error
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al agregar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agrega un nuevo item a la checklist de escrituración.
     */
    public function agregarItemEscrituracion(Request $request, ProcesoVenta $proceso)
    {
        $request->validate(['nombre' => 'required|string|max:255']);
        try {
            DB::beginTransaction();
            if ($request->ajax()) {
                $item = $proceso->escrituracionItems()->create($request->only('nombre'));
                DB::commit();
                $item->refresh();
                return response()->json([
                    'success' => true,
                    'message' => 'Item agregado a la checklist de escrituración.',
                    'item' => $item
                ]);
            }
            // Si no es una petición AJAX, redirigimos con un mensaje de éxito
            DB::rollBack();
            return back()->with('error', 'Error al agregar item de escrituración.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al agregar el item de escrituración: ' . $e->getMessage()]);

            // Si la petición es AJAX, devolvemos JSON con error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrió un error al agregar el item de escrituración: ' . $e->getMessage()
                ], 500);
            }

            // Si no es AJAX, redirigimos con un mensaje de error
            return back()->with('error', 'Ocurrió un error al agregar el item de escrituración.');
        }
    }

    public function updateDocumentoItem(Request $request, DocumentacionItem $item)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,entregado,en_revision,aprobado',
            'observaciones' => 'nullable|string',
        ]);

        $item->update([
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item actualizado correctamente.',
            'item' => $item, // Devolvemos el item actualizado
        ]);
    }

    public function updateEscrituracionItem(Request $request, EscrituracionItem $item)
    {
        $request->validate([
            'estado' => 'required|in:0,1',
            'observaciones' => 'nullable|string',
        ]);

        $item->update([
            'completado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item actualizado correctamente.',
            'item' => $item, // Devolvemos el item actualizado
        ]);
    }

    /**
     * Elimina un item de la checklist.
     */
    public function destroyDocumentoItem(DocumentacionItem $item)
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item eliminado correctamente.',
        ]);
    }

    /**
     * Elimina un item de la checklist.
     */
    public function destroyEscrituracionItem(EscrituracionItem $item)
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item eliminado correctamente.',
        ]);
    }

    public function storeEtapaPeritajeAprobacion(Request $request, ProcesoVenta $proceso)
    {
        try {
            DB::beginTransaction();

            if ($request->has('visita_peritaje') && $request->input('visita_peritaje')) {
                $proceso->visita_perito = $request->input('visita_peritaje');
            }

            if ($request->has('aprobacion_credito') && $request->input('aprobacion_credito')) {
                $proceso->aprobacion_credito = $request->input('aprobacion_credito');
            }

            // Actualizar la etapa actual si la etapa de peritaje está marcada como completa
            if ($request->has('etapa_peritaje_completa') && $request->input('etapa_peritaje_completa')) {
                $proceso->etapa_actual = 'Escrituracion';
            }

            $proceso->observaciones_peritaje = $request->observaciones;

            $proceso->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Etapa de Peritaje y Aprobación actualizada exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al actualizar la etapa de peritaje y aprobación: ' . $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar la etapa de peritaje y aprobación']);
        }
    }

    public function storeEtapaDesembolso(Request $request, ProcesoVenta $proceso)
    {
        $request->validate(
            [
                'valor' => 'required',
                'observaciones' => 'nullable|string',
            ],
            [
                'valor.required' => 'Ingrese un valor.',
            ]
        );
        try {
            DB::beginTransaction();
            $proceso->monto_desembolsado = limpiarValor($request->valor);
            $proceso->observaciones_desembolso = $request->observaciones;

            $proceso->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Etapa de Desmbolso actualizada exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al actualizar la etapa de desembolso: ' . $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar la etapa de desembolso']);
        }
    }

    public function storeEtapaEntrega(Request $request, ProcesoVenta $proceso)
    {
        try {
            DB::beginTransaction();
            $etapa_completa = $request->has('etapa_entrega_completa') && $request->input('etapa_entrega_completa');
            if ($etapa_completa) {
                $proceso->etapa_actual = 'Entrega';
            }

            $proceso->contrato()->updateOrCreate(
                ['titulo' => 'acta de entrega'],
                ['contenido' => $request->input('contenido_acta_entrega')]
            );

            $carpetaDestino = $this->path_files . 'entrega/' . date('Y-m-d') . '/' .  Str::slug($proceso->cliente->nombre, '_');
            $old_file_path = '';
            // Manejar las subidas de archivos
            if ($request->hasFile('file_contrato_entrega_firmado')) {
                $path_archivo = subirArchivo($carpetaDestino, 'contrato_entrega_firmado', $request->file('file_contrato_entrega_firmado'));

                $old_file_path  = $proceso->contrato_entrega_firmado_path;
                $proceso->contrato_entrega_firmado_path = $path_archivo;
            }

            $proceso->save();

            DB::commit();
            if ($old_file_path && Storage::disk('digitalocean')->exists($old_file_path)) {
                Storage::disk('digitalocean')->delete($old_file_path);
                LogService::log('INFO', 'Seguimiento ventas', ['message' => 'Archivo antiguo eliminado: ' . $old_file_path]);
            }
            return response()->json(['success' => true, 'message' => 'Etapa de Entrega actualizada exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al actualizar la etapa de entrega: ' . $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar la etapa de entrega']);
        }
    }

    public function storeValorSaldoReserva(Request $request)
    {
        try {
            DB::beginTransaction();
            $proceso = ProcesoVenta::find($request->input('proceso'));
            $proceso->valor_saldo_reserva = limpiarValor($request->valor);

            $proceso->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'valor saldo reserva ingresado exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al actualizar la etapa de desembolso: ' . $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar la etapa de desembolso']);
        }
    }

    public function actualizaEtapa(Request $request, ProcesoVenta $proceso)
    {
        if ($request->ajax()) {
            $isChecked = $request->input('valor');

            try {
                DB::beginTransaction();
                switch ($request->input('etapa')) {
                    case 'etapa-documentacion-completa':
                        if ($isChecked) {
                            $proceso->etapa_actual = 'Documentación inicial';
                        } else {
                            $proceso->etapa_actual = 'Reserva';
                        }
                        break;
                    case 'etapa-escrituracion-completa':
                        if ($isChecked) {
                            $proceso->etapa_actual = 'Escrituración';
                        } else {
                            $proceso->etapa_actual = 'Documentación inicial';
                        }
                        break;
                    default:
                        return response()->json(['success' => false, 'message' => 'Etapa no reconocida.']);
                        break;
                }

                $proceso->save();

                DB::commit();

                return response()->json(['success' => true, 'message' => 'Etapa actualizada exitosamente.']);
            } catch (\Exception $e) {
                DB::rollBack();
                LogService::log('ERROR', 'Seguimiento ventas', ['message' => 'Ocurrió un error al actualizar la etapa: ' . $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Ocurrió un error al actualizar la etapa']);
            }
        }
        abort(404);
    }
}