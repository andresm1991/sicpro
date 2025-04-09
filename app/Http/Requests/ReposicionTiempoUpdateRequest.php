<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReposicionTiempoUpdateRequest extends FormRequest
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
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'detalle' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'user.required' => 'Seleccione el colaborador.',
            'fecha.required' => 'Seleccione la fecha.',
            'hora_inicio.required' => 'La hora de inicio es requerida',
            'hora_fin.required' => 'La hora de fin es requerida',
            'detalle.required' => 'El detalle es requerido',
        ];
    }
}
