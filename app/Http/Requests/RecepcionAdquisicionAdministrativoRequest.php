<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecepcionAdquisicionAdministrativoRequest extends FormRequest
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
        return [
            'proveedor' => 'required',
            'cantidad_recibida' => 'required|array',
            'cantidad_recibida.*' => 'required',
            'unidad_medida' => 'required|array',
            'unidad_medida.*' => 'required',
            'valor' => 'required|array',
            'valor.*' => 'required',
            'forma_pago' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'proveedor.required' => 'Seleccione el proveedor.',
            'cantidad_recibida.required' => 'Ingrese cantidad.',
            'cantidad_recibida.*.required' => 'Ingrese cantidad.',
            'unidad_medida.required' => 'Seleccione opción.',
            'unidad_medida.*.required' => 'Seleccione opción.',
            'valor.required' => 'Ingrese valor.',
            'valor.*.required' => 'Ingrese valor',
            'forma_pago.required' => 'Seleccione la forma de pago.',
        ];
    }
}
