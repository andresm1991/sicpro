<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdquisicionAdministrativoRequest extends FormRequest
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
            'proyecto' => 'required',
            'etapa' => 'required',
            'actividad' => 'required',
            'productos' => 'required|array',
            'productos.*' => 'required',
            'cantidad' => 'required|array',
            'cantidad.*' => 'required',
            'necesidad' => 'required|array',
            'necesidad.*' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'proyecto.required' => 'Selecione el proyecto.',
            'etapa.required' => 'Seleccione la etapa.',
            'actividad.required' => 'Selecciones la actividad.',
        ];
    }
}
