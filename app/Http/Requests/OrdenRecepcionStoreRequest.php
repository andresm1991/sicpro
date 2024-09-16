<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrdenRecepcionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules =  [
            'proveedor' => 'required|numeric',
            'pedido' => 'required|numeric',
            'cantidad_recibida' => 'required|array', // Valida que sea un array
            'cantidad_recibida.*' => 'required|numeric', // Valida que cada elemento dentro del array sea numérico'cantidad_recibida' => 'required|array|numeric',
            'forma_pago' => 'required',
        ];

        if ($this->get('orden_completa')) {
            $rules = array_merge($rules, ['forma_pago' => 'required']);
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'proveedor.required' => 'Seleccione el proveedor.',

            // Mensajes personalizados para los elementos del array cantidad_recibida
            'cantidad_recibida.required' => 'Ingrese al menos un valor de cantidad recibida.',
            'cantidad_recibida.*.required' => 'Ingrese el valor de cantidad recibida.',
            'cantidad_recibida.*.numeric' => 'El valor de cada cantidad recibida debe ser numérico.',

            'forma_pago.required' => 'Seleccione la forma de pago.'
        ];
    }
}