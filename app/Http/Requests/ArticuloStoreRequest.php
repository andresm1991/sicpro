<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticuloStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'categoria' => 'required',
            'codigo' => 'unique:articulos',
            'descripcion' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'categoria.required' => 'Seleccione una opción.',
            'codigo.unique' => 'El codigo ingresado ya está en uso.',
            'descripcion.required' => 'Ingrese la descripción del producto.'
        ];
    }
}