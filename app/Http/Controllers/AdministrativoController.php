<?php

namespace App\Http\Controllers;

use App\Models\Adquisicion;
use App\Models\OrdenRecepcion;
use App\Services\LogService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdministrativoController extends Controller
{
    public function index( ){
        $title_page = 'Administrativo';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => ''],
        ];

        return view('administrativo.menu_administrativo', compact('title_page', 'breadcrumbs'));

    }

    public function adquisiciones ($tipo) {
        $title_page = ($tipo == 'operativo') ? 'Adquisiciones Operativas' : 'Adquisiciones Administrativas';
        $adquisiciones = Adquisicion::where('tipo_adquisicion', $tipo)->orderBy('fecha', 'desc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => Route('administrativo.index')],
            ['name' => $title_page, 'url' => '']
        ];

        return view('administrativo.adquisiciones.index', compact('adquisiciones', 'tipo', 'title_page', 'breadcrumbs'));
    }

    public function editarAdquisicion ($tipo, Adquisicion $adquisicion)
    {        
        if(strtolower($adquisicion->estado) != "finalizado"){
            return redirect()->back()->with('toast_error', 'No puede editar la información de esta adquisión porque aun no se ha finalizado.');
        }
        $title_page = 'Editar';
        
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones', 'url' => route('administrativo.adquisiciones', $tipo)],
            ['name' => 'Editar', 'url' => '']
        ];

        $totalGeneral = $adquisicion->adquisiciones_detalle->sum(function ($detalle) {
            return $detalle->cantidad_recibida * $detalle->valor;
        });

        return view('administrativo.adquisiciones.edit', compact('adquisicion', 'tipo', 'totalGeneral','title_page', 'breadcrumbs'));
    } 
    public function actualizarAdquisicion (Request $request, $tipo, Adquisicion $adquisicion) 
    {
        // Limpia el símbolo de dólar de cada elemento en el arreglo 'valor'
        $valoresLimpios = array_map(function($value) {
            return preg_replace('/[^0-9.]/', '', $value); // Elimina $ y otros caracteres no numéricos
        }, $request->input('valor', []));

        // Reemplaza los valores en el request con los valores limpios
        $request->merge(['valor' => $valoresLimpios]);

        $request->validate(
            [
                'unidad_medida' => 'required|array',
                'unidad_medida.*' => 'required', 
                'valor' => 'required|array',
                'valor.*' => 'required|numeric', 
            ],
            [
                'unidad_medida.required' => 'Seleccione una opción.',
                'unidad_medida.*.required' => 'Seleccione una opción.',
                'valor.required' => 'Ingrese el valor',
                'valor.*.required' => 'Ingrese el valor',
                'valor.*.numeric' => 'El valor ingresado es inválido,'
            ]
        );

        $array_unidad_medida = $request->unidad_medida;
        $array_valor_unidatrio = $request->valor;
        $unidadMedidaCase = "CASE";
        $valorCase = "CASE";
        $ids = [];

        try {
            
            foreach ($adquisicion->adquisiciones_detalle as $index => $detalle) {
                if(!is_numeric($array_unidad_medida[$index])){
                    $id = registrarUnidadMedida($array_unidad_medida[$index]);
                }else{
                    $id = $array_unidad_medida[$index];
                }
                $valor = quitarSimboloUSD($array_valor_unidatrio[$index]);
                
                $unidadMedidaCase .= " WHEN id = {$detalle->id} THEN '{$id}'";
                $valorCase .= " WHEN id = {$detalle->id} THEN {$valor}";
                $ids[] = $detalle->id;
            }

            $unidadMedidaCase .= " END";
            $valorCase .= " END";

            DB::beginTransaction();
            DB::table('adquisiciones_detalle')
                ->whereIn('id', $ids)
                ->update([
                    'unidad_medida_id' => DB::raw($unidadMedidaCase),
                    'valor' => DB::raw($valorCase)
                ]);
            DB::commit();
            LogService::log('info', 'Actualizacion la informacion de la adquisicion #'.$adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update']);

            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('success', 'Se actualizó la información de la adquisición con éxito.');
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al actualizar la inforacion de la adquisicion #'.$adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }
}
