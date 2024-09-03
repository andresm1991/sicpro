<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;

class CatalogoDatoController extends Controller
{
    public function getBancos(Request $request)
    {
        if ($request->ajax()) {
            $padre = CatalogoDato::getCatalogoPadre('bancos');
            $bancos = CatalogoDato::where('padre_id', $padre->id)->pluck('descripcion', 'id');
            foreach ($bancos as $key => $value) {
                $result[] = ['id' => $key, 'text' => $value];
            }

            return $result;
        }
    }

    public function getTipoCuentas(Request $request)
    {
        if ($request->ajax()) {
            $padre = CatalogoDato::getCatalogoPadre('tipo.cuentas');
            $bancos = CatalogoDato::where('padre_id', $padre->id)->pluck('descripcion', 'id');
            foreach ($bancos as $key => $value) {
                $result[] = ['id' => $key, 'text' => $value];
            }

            return $result;
        }
    }

    public function getCategoriaManoObra(Request $request)
    {
        if ($request->ajax()) {
            $categoria = CatalogoDato::where('padre_id', 33)->pluck('descripcion', 'id');
            foreach ($categoria as $key => $value) {
                $result[] = ['id' => $key, 'text' => $value];
            }

            return $result;
        }
    }
}
