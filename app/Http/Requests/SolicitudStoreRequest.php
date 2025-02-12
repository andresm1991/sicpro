<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudStoreRequest extends FormRequest
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
            'user' => 'required',
            'tipo_solicitud' => 'required',
            'estado_solicitud' => 'required',
            'fecha_desde' => 'required|date|before_or_equal:fecha_hasta',
            'fecha_hasta' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i|before_or_equal:hora_fin',
            'hora_fin' => 'required|date_format:H:i',
            'detalle' => 'required',
        ];
    }

    public function messages()
    {
        return[
            'user.required' => 'Seleccione el colcaborador.',
            'tipo_solicitud.required' => 'Selecione una opción.',
            'estado_solicitud.required' => 'Seleccione una opción.',
            'fecha_desde.required' => 'Ingrese la fecha de inicio.',
            'fecha_desde.before_or_equal' => 'Esta fecha debe ser una fecha anterior o igual a fecha hasta.',
            'fecha_hasta.required' => 'Ingrese la fecha final.',
            'hora_inicio.required' =>'Igrese la hora de inicio.',
            'hora_inicio.before_or_equal' =>'La hora debe ser una hora anterior o igual a hora hasta.',
            'hora_fin.required' => 'Ingrese la hora final.',
            'detalle.required' => 'Ingrese el detalle de la solicitud.',
        ];
    }
}
