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
            'cantidad_recibida' => 'required|array',
            'cantidad_recibida.*' => 'required|numeric', 
            'forma_pago' => 'required',
        ];

        if(strtoupper($this->tipo_etapa) == 3){
            $rules = array_merge($rules, [
                'unidad_medida' => 'required|array',
                'unidad_medida.*' => 'required', 
                'valor' => 'required|array',
                'valor.*' => 'required|numeric',]);
        }
        if ($this->get('orden_completa')) {
            $rules = array_merge($rules, ['forma_pago' => 'required']);
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'proveedor.required' => 'Seleccione el proveedor.',
            'proveeor.numeric' => 'Opción inválida.',
            'cantidad_recibida.required' => 'Ingrese al menos un valor de cantidad recibida.',
            'cantidad_recibida.*.required' => 'Ingrese el valor de cantidad recibida.',
            'cantidad_recibida.*.numeric' => 'El valor de cada cantidad recibida debe ser numérico.',
            'unidad_medida.*.required' => 'selecione la unidad de medida',
            'valor.*.required' => 'Ingrese el valor unitario.',
            'forma_pago.required' => 'Seleccione la forma de pago.'
        ];
    }
}
