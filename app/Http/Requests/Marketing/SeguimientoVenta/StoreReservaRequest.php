<?php

namespace App\Http\Requests\Marketing\SeguimientoVenta;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Verificamos si el campo 'valor_reserva' existe y no está vacío
        if ($this->has('valor_reserva')) {
            // Reemplazamos todas las comas (separadores de miles) por nada
            // Y nos aseguramos de que los decimales (si los hay) usen un punto.
            // Esto transforma '1,111,111.11' en '1111111.11'
            $valorLimpio = str_replace(',', '', $this->input('valor_reserva'));

            // Actualizamos el valor en el request
            $this->merge([
                'valor_reserva' => $valorLimpio,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:13|unique:clientes_ventas,documento',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'valor_reserva' => 'nullable|numeric|min:0',
            'file_contrato_firmado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'fila_comprobante_pago_reserva' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documentos_identidad.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'Ingrese nombre.',
            'documento_identidad.required' => 'Ingrese numero de documento.',
            'documento_identidad.unique' => 'El documento de identidad ya se encuentra registrado.'
        ];
    }
}
