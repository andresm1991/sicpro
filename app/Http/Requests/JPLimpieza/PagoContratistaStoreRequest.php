<?php

namespace App\Http\Requests\JPLimpieza;

use Illuminate\Foundation\Http\FormRequest;

class PagoContratistaStoreRequest extends FormRequest
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
            'monto' => ['required', 'regex:/^\d{1,3}(,\d{3})*(\.\d{1,4})?$/'],
            'tipo_pago' => 'required',
            'forma_pago' => 'required',
            'estado' => 'required',
            'fecha' => 'required|date',
            'detalle' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'monto.required' => 'Ingrese monto.',
            'monto.regex' => 'El monto debe ser un número válido.',
            'tipo_pago.required' => 'Seleccione opción.',
            'forma_pago_id.required' => 'Seleccione opción.',
            'estado_id.required' => 'Seleccione opción.',
            'fecha.required' => 'La fecha es obligatorio.',
            'fecha.date' => 'La fecha debe ser una fecha válida.',
            'detalle.required' => 'Ingrese detalle.',
        ];
    }
}
