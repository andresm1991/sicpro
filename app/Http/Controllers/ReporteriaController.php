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
            $proveedores = Proveedor::whereHas('categoria_proveedor', function ($query) use ($tipo_proveedor) {
                $query->where('slug', $tipo_proveedor);
            })->orderBy('razon_social', 'asc')
                ->get(['id', 'razon_social']);

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

        return view('reportes.gasolina_camioneta', compact('title_page', 'breadcrumbs',  'proyectos', 'necesidades'));
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

        return view('reportes.eventaulidad_reposicion', compact('title_page', 'breadcrumbs', 'tipo_solicitudes',  'estados'));
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

        return view('reportes.caja', compact('title_page', 'breadcrumbs'));
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
}
