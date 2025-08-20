<?php

namespace App\Http\Requests\JPLimpieza;

use Illuminate\Foundation\Http\FormRequest;

class ProyectoStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Limpiar el campo precio_metro: eliminar comas y otros caracteres no numéricos
        $this->merge([
            'precio_metro' => str_replace(',', '', $this->input('precio_metro')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_proyecto' => 'required|string|max:255',
            'entidad' => 'required|string|max:255',
            'metraje_contratado' => 'required|numeric|min:0',
            'precio_metro' => 'required|numeric|min:0',
            'tiempo_contratado' => 'required|integer|min:1',
            'portada' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'archivo_orden_compra' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'archivo_acta_final' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ];
    }
}
