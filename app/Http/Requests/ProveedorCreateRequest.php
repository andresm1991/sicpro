<?php

namespace App\Http\Requests;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProveedorCreateRequest extends FormRequest
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
        $rules = [
            'documento' => ['required', 'numeric', 'digits_between:10,13', Rule::unique('proveedores')->where(function ($query) {
                return $query->where('categoria_proveedor_id', $this->menu_id);
            })],
            'nombres' => 'required',
            'direccion' => 'required',
            'email' => 'email',
        ];


        return $rules;
    }

    public function messages()
    {
        return [
            'documento.required' => 'Por favor ingrese un RUC o CI.',
            'documento.numeric' => 'RUC o CI debe ser numérico.',
            'documento.digits_between' => 'Por favor ingrese entre 10 y 13 dígitos.',
            'documento.unique' => 'El RUC o CI ya se encuentra registrado.',
            'nombres.required' => 'Por favor ingrese el nombre y apellido.',
            'direccion.required' => 'Por favor ingrese una dirección.',
            'email.email' => 'Ingrese una dirección de correo válida.'
        ];
    }
}