<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\ManoObra;
use App\Models\Proyecto;
use App\Models\Proveedor;
use App\Models\Adquisicion;
use App\Models\AdquisicionDetalle;
use App\Models\Contratista;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use App\Models\DiccionarioPalabra;
use App\Models\MovimientoCaja;
use App\Models\ReposicionTiempo;
use App\Models\RevisionCaja;
use App\Models\Solicitud;
use App\Models\User;
use Carbon\Carbon;

class ReporteriaController extends Controller
{
    public function index(Request $request)
    {
        $title_page = 'Reportes';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => '']
        ];

        return view('reportes.index', compact('title_page', 'breadcrumbs'));
    }

    public function reporteAdquisiciones()
    {
        $title_page = 'Reportes Adquisiciones';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('reporte.index')],
            ['name' => 'Adquisiciones', 'url' => ''],
        ];

        $proyectos = Proyecto::orderBy('nombre_proyecto', 'asc')->pluck('nombre_proyecto', 'id')->prepend('', '');
        $proyectos = $proyectos->toArray(); // Convertir a array
        $proyectos['0'] = 'GENERAL'; // Añadir el nuevo elemento al final
        $proyectos = collect($proyectos); // Convertir nuevamente a colección si es necesario

        //$proyectos = Proyecto::pluck('nombre_proyecto', 'id')->prepend('', '');
        $etapas = CatalogoDato::getChildrenCatalogo('menu.adquisiciones')->pluck('descripcion', 'id')->prepend('', '');
        $tipoAdquisiciones = CatalogoDato::getChildrenCatalogo('proveedor')->where('slug', '!=', 'profecionales')->pluck('descripcion', 'id')->prepend('', '');
        $necesidades = DiccionarioPalabra::pluck('palabra', 'palabra')->prepend('', '');

        return view('reportes.adquisiciones', compact('title_page', 'breadcrumbs', 'proyectos', 'etapas', 'tipoAdquisiciones', 'necesidades'));
    }

    public function filtroReportesAdquisiciones(Request $request)
    {
        if ($request->ajax()) {
            $tipo = $request->input('tipo');
            $result = ['subproyecto' => null, 'proveedores' => null, 'productos' => null, 'cargos' => null];

            $tipo_proveedor = $tipo == 'materiales_y_herramientas' ? 'meteriales.herramientas' : ($tipo == 'servicios' ? 'servicios' : ($tipo == 'contratistas' ? 'contratista' : ($tipo == 'mano_de_obra' ? 'mano.obra' : '')));

            /// Obtener los proveedores segun la categoria
            $proveedoresCollection = Proveedor::whereHas('categoria_proveedor', function ($query) use ($tipo_proveedor) {
                $query->where('slug', $tipo_proveedor);
            })
                ->get(['id', 'razon_social', 'nombres', 'apellidos']);
            $proveedores = $proveedoresCollection->sortBy('nombre_proveedor')->values();

            if ($tipo == 'materiales_y_herramientas' || $tipo == 'servicios' || $tipo == 'contratistas') {
                $result['proveedores'] = $proveedores;
                if ($tipo != 'contratistas') {
                    $productos = Articulo::where('activo', true)->whereHas('categoria_articulo', function ($query) use ($tipo) {
                        $query->where('slug', $tipo == 'materiales_y_herramientas' ? 'tipo.adquisiciones.bienes' : 'tipo.adquisiciones.servicios');
                    })->orderby('descripcion', 'asc')->get(['id', 'descripcion']);
                    $result['productos'] = $productos;
                }
            } elseif ($tipo == 'mano_de_obra') {
                $cargos = Articulo::where('activo', true)->whereHas('categoria_articulo', function ($query) {
                    $query->where('slug', 'tipo.adquisiciones.servicios');
                })->orderBy('descripcion', 'asc')->get(['id', 'descripcion']);
                $result['cargos'] = $cargos;
            }

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        }
    }

    public function filtroSubProyectosAdquisiciones(Request $request)
    {
        if ($request->ajax()) {
            $proyectoId = $request->proyecto;
            $tipoId = $request->input('tipo');
            $result = [];
            if ($tipoId) {
                $tipo = CatalogoDato::find($tipoId);
                if ($tipo->slug == 'contratista') {
                    $subproyecto = Contratista::where('proveedor_id', $proyectoId)->where('subproyecto', '!=', null)
                        ->pluck('subproyecto', 'subproyecto')->prepend('', '');
                } elseif ($tipo->slug == 'mano.obra') {
                    $subproyecto = ManoObra::where('proyecto_id', $proyectoId)->where('subproyecto', '!=', null)
                        ->pluck('subproyecto', 'subproyecto')->prepend('', '');
                } else {
                    $subproyecto = Adquisicion::where('proyecto_id', $proyectoId)
                        ->where('tipo_etapa_id', $tipo->id)->where('subproyecto', '!=', null)
                        ->pluck('subproyecto', 'subproyecto')->prepend('', '');
                }
            } else {
                $proyecto = Proyecto::with(['adquisiciones', 'mano_obra', 'contratista'])->find($proyectoId);

                $subproyectos = collect();

                // Adquisiciones
                $subproyectos = $subproyectos->merge(
                    $proyecto->adquisiciones->whereNotNull('subproyecto')->pluck('subproyecto')
                );

                // Mano de Obra
                $subproyectos = $subproyectos->merge(
                    $proyecto->mano_obra->whereNotNull('subproyecto')->pluck('subproyecto')
                );

                // Contratistas
                $subproyectos = $subproyectos->merge(
                    $proyecto->contratista->whereNotNull('subproyecto')->pluck('subproyecto')
                );

                // Eliminar duplicados y reindexar
                $subproyectos = $subproyectos->unique()->values();

                // Si quieres un pluck tipo ['subproyecto' => 'subproyecto']
                $subproyecto = $subproyectos->mapWithKeys(function ($item) {
                    return [$item => $item];
                })->prepend('', '');
            }

            return response()->json([
                'success' => true,
                'subproyectos' => $subproyecto,
            ]);
        }
    }

    /**
     * Método para visualizar el reporte de adquisiciones
     * @return \Illuminate\Http\Response
     */
    public function visualizarReporteAdquisiciones(Request $request)
    {
        try {
            $tipo = $request->input('tipo');
            $tipo_reporte = $request->input('tipo_reporte');

            if ($tipo_reporte == 'global') {
                $query = Adquisicion::reporteGlobalAdquisiciones($request);
                return response()->json([
                    'success' => true,
                    'result' => $query,
                ]);
            } elseif ($tipo_reporte == 'comparativo') {
                $query = Adquisicion::reporteComparativoAdquisiciones($request);
                if (isset($query['error'])) {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'Error al generar el reporte: ' . $query['error'],
                    ]);
                }
            } else {
                $tipoAdquisisicon = CatalogoDato::find($tipo);
                if ($tipoAdquisisicon->slug == 'meteriales.herramientas' || $tipoAdquisisicon->slug == 'servicios') {
                    $query = Adquisicion::dataReporteAdquisiciones($request);
                } elseif ($tipoAdquisisicon->slug == 'contratista') {
                    $query = Contratista::filtroContratista($request);
                } else {
                    $query = ManoObra::filtroManoObra($request);
                }
            }

            return response()->json([
                'success' => true,
                'result' => $query,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al generar el reporte: ' . $th->getMessage(),
                'error' => $th->getLine(),
            ]);
        }
    }

    /**
     * Método para visualizar el reporte de adquisiciones de gasolina camioneta
     * @return \Illuminate\Http\Response
     */
    public function reporteGasolinaCamioneta()
    {
        $title_page = 'Reporte Gasolina Camioneta';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('reporte.index')],
            ['name' => 'Gasolina Camioneta', 'url' => ''],
        ];

        $proyectos = Proyecto::orderBy('nombre_proyecto', 'asc')->pluck('nombre_proyecto', 'id')->prepend('', '');
        $proyectos = $proyectos->toArray(); // Convertir a array
        $proyectos['0'] = 'GENERAL'; // Añadir el nuevo elemento al final
        $proyectos = collect($proyectos); // Convertir nuevamente a colección si es necesario

        $necesidades = DiccionarioPalabra::pluck('palabra', 'palabra')->prepend('', '');

        return view('reportes.gasolina_camioneta', compact('title_page', 'breadcrumbs', 'proyectos', 'necesidades'));
    }

    public function visualizarReporteGasolinaCamioneta(Request $request)
    {
        try {

            $query = Adquisicion::dataReporteAdquisiciones($request, true);
            $result = new \Illuminate\Support\Collection();
            $fecha_inicio = '';
            $km_anterior = 0;

            foreach ($query as $index => $item) {
                $galones = $item['adquisicion']->adquisiciones_detalle->first()->cantidad_solicitada;
                $km_carga = $item['adquisicion']->adquisiciones_detalle->first()->kilometraje;
                $km_recorrido = $km_anterior > 0 ? $km_carga - $km_anterior : 0;
                $km_galon = $km_recorrido / $galones;
                $valor = $item['adquisicion']->adquisiciones_detalle->first()->valor ?? 0;
                if ($valor > 0) {
                    $iva = $item['adquisicion']->adquisiciones_detalle->first()->iva ?? 0;
                    $valor = calcularTotalProducto($galones, $valor, $iva);
                }

                $result->add([
                    'fecha' => Carbon::createFromFormat('Y-m-d', $item['adquisicion']->fecha)->format('d-m-Y'),
                    'dias' => $index > 0 ? diasEntreFechas($fecha_inicio, $item['adquisicion']->fecha) : '',
                    'km_carga' => $km_carga,
                    'km_anterior' => $km_anterior,
                    'km_recorrido' => $km_recorrido,
                    'km_galon' => $km_galon,
                    'valor' => '$ ' . number_format($valor, 4),
                    'galones' => $galones,
                ]);

                $fecha_inicio = $item['adquisicion']->fecha;
                $km_anterior = $item['adquisicion']->adquisiciones_detalle->first()->kilometraje;
            }
            // return $result;
            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al generar el reporte: ' . $th->getMessage(),
                'error' => $th->getLine(),
            ]);
        }
    }


    public function reporteAusenciasReposiciones()
    {
        $title_page = 'Reporte Solicitudes';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('reporte.index')],
            ['name' => 'solicitudes', 'url' => ''],
        ];

        $tipo_solicitudes = CatalogoDato::getChildrenCatalogo('tipo.solicitudes')->pluck('descripcion', 'id')->prepend('', '');
        $tipo_solicitudes = $tipo_solicitudes->toArray(); // Convertir a array
        $tipo_solicitudes['reposiciones_global'] = 'reposiciones global';
        $tipo_solicitudes['reposiciones_detallado'] = 'reposiciones detallado';
        $tipo_solicitudes = collect($tipo_solicitudes); // Convertir nuevamente a colección si es necesario
        $estados = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id')->prepend('', '');

        return view('reportes.eventaulidad_reposicion', compact('title_page', 'breadcrumbs', 'tipo_solicitudes', 'estados'));
    }

    public function visualizarSolicitudes(Request $request)
    {
        try {
            $tipo_solicitud = $request->input('tipo_solicitud');
            if ($tipo_solicitud != 'reposiciones_global' && $tipo_solicitud != 'reposiciones_detallado') {
                $query = Solicitud::dataReporteSolicitudes($request);
            } else {
                $query = ReposicionTiempo::getTotalReposiciones($request);
            }
            // return $result;
            return response()->json([
                'success' => true,
                'result' => $query,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al generar el reporte: ' . $th->getMessage(),
                'error' => $th->getLine(),
            ]);
        }
    }

    //** Reporte de caja (Flujo de efectivo) */
    public function reporteCaja()
    {
        $title_page = 'Reporte Caja';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('reporte.index')],
            ['name' => 'Caja', 'url' => ''],
        ];

        $ultima_revision_caja = RevisionCaja::latest()->first();


        return view('reportes.caja', compact('title_page', 'breadcrumbs', 'ultima_revision_caja'));
    }

    public function visualizarReporteCaja(Request $request)
    {
        try {
            $query = MovimientoCaja::dataReporteCaja($request);
            // return $result;
            return response()->json([
                'success' => true,
                'result' => $query,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al generar el reporte: ' . $th->getMessage(),
                'error' => $th->getLine(),
            ]);
        }
    }

    public function guardarRevisionCaja(Request $request)
    {
        try {
            $data = $request->validate([
                'fechas' => 'required',
                'fechas.*' => 'required|date',
            ]);

            list($fechaInicio, $fechaFin) = explode(' - ', $request->fechas);

            $data = [
                'fecha_revision_inicio' => $fechaInicio,
                'fecha_revision_fin' => $fechaFin,
                'usuario_id' => auth()->id(),
                'observaciones' => $request->observaciones,
            ];

            RevisionCaja::create($data);

            return response()->json([
                'success' => true,
                'mensaje' => 'Revisión de caja guardada exitosamente.',
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al guardar la revisión de caja: ' . $th->getMessage(),
                'error' => $th->getLine(),
            ]);
        }
    }

    /**
     * Obtener el detalle de materiales, servicios, contratistas cuando se hace clic en un elemento.
     * petición AJAX.
     */
    public function obtenerDetalle(Request $request)
    {

        try {
            $tipo_detalle = $request->input('tipo_detalle');

            $query = [];
            $html = '';
            switch ($tipo_detalle) {
                case 'contratista':
                    $query = Contratista::filtroContratista($request);
                    $html = $this->htmlContratista($query);
                    break;
                // Agregar más casos según sea necesario
                case 'materiales':
                    $query = Adquisicion::dataReporteAdquisiciones($request);
                    $html = $this->htmladquisiciones($query);
                    break;
                case 'servicios':
                    $query = Adquisicion::dataReporteAdquisiciones($request);
                    $html = $this->htmladquisiciones($query);
                    break;
            }

            return response()->json([
                'success' => true,
                'result' => $html,
                'data' => $query,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el detalle: ' . $th->getMessage(),
                'error' => $th->getLine(),
            ]);
        }
    }

    private function htmlContratista($query)
    {
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm">';
        $html .= '<thead><tr><th>Orden Nro.</th><th>Subproyecto</th><th>Etapa</th><th>Tipo</th><th>Valor contratado</th><th>Avances</th><th>Saldo</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($query as $item) {
            $html .= '<tr>';
            $html .= '<td class="align-middle"><a href="' . route('pdf.orden.trabajo.contratista', $item['contratista']->id) . '" class="text-dark" target="_blank">' . numeroOrden($item['contratista'], false) . '</a></td>';
            $html .= '<td class="align-middle">' . ($item['contratista']->subproyecto ?? '') . '</td>';
            $html .= '<td class="align-middle">' . ($item['contratista']->etapa->descripcion ?? '') . '</td>';
            $html .= '<td class="align-middle">' . ($item['contratista']->tipo_etapa->descripcion ?? '') . '</td>';
            $html .= '<td class="align-middle">$ ' . number_format($item['contratista']->total_contratistas, 2) . '</td>';
            $html .= '<td class="align-middle">$ ' . number_format($item['contratista']->pagos_contratistas, 2) . '</td>';
            $html .= '<td class="align-middle">$ ' . number_format($item['contratista']->total_contratistas - $item['contratista']->pagos_contratistas, 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        return $html;
    }

    private function htmladquisiciones($query)
    {
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-sm">';
        $html .= '<thead><tr><th>Orden Nro.</th><th>Fecha Pedido</th><th>Subproyecto</th><th>Etapa</th><th>Tipo</th><th>Estado</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($query as $item) {
            $html .= '<tr>';
            $html .= '<td class="align-middle"><a href="' . route('pdf.recepcion', $item['adquisicion']->id) . '" class="text-dark" target="_blank">' . numeroOrden($item['adquisicion'], false) . '</a></td>';
            $html .= '<td class="align-middle">' . (date('d-m-Y', strtotime($item['adquisicion']->fecha)) ?? '') . '</td>';
            $html .= '<td class="align-middle">' . ($item['adquisicion']->subproyecto ?? '') . '</td>';
            $html .= '<td class="align-middle">' . ($item['adquisicion']->etapa->descripcion ?? '') . '</td>';
            $html .= '<td class="align-middle">' . ($item['adquisicion']->tipo_etapa->descripcion ?? '') . '</td>';
            $html .= '<td class="align-middle">' . ($item['adquisicion']->estado ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        return $html;
    }
}
