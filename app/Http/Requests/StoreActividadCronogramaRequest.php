<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActividadCronogramaRequest extends FormRequest
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
            'lunes' => 'nullable|array', // Validar que sea un array
            'martes' => 'nullable|array',
            'miercoles' => 'nullable|array',
            'jueves' => 'nullable|array',
            'viernes' => 'nullable|array',
            'sabado' => 'nullable|array',
        ];
    }
}