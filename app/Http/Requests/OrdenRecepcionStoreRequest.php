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
            'cantidad_recibida' => 'required|numeric'
        ];

        if ($this->get('orden_completa')) {
            $rules = array_merge($rules, ['forma_pago' => 'required']);
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'proveedor.required' => 'Selecione el proveedor.',
            'cantidad_recibida.required' => 'Ingrese un valor.',
            'cantidad_recibida.numeric' => 'El valor debe ser numerico.',
            'forma_pago' => 'Seleccione la forma de pago.'
        ];
    }
}