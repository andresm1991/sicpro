<?php

namespace App\Http\Requests;

use App\Models\Articulo;
use Illuminate\Foundation\Http\FormRequest;

class AdquisicionStoreRequest extends FormRequest
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
        $rules = [
            'productos' => 'required|array',
            'productos.*' => 'required',
            'cantidad' => 'required|array',
            'cantidad.*' => 'required',
            'necesidad' => 'required|array',
            'necesidad.*' => 'required',
        ];

        if (in_array('gasolina para camnioneta', $this->input('productos', []))) {
            $rules['km'] = 'required|array'; // Asegura que km es un array
            $rules['km.*'] = 'numeric|min:0'; // Aplica las reglas necesarias para cada valor en km
        }else{
            // Verificar si hay algún producto de tipo "gasolina" en el array de IDs
            $hasGasolina = Articulo::whereIn('id', $this->input('productos', []))
            ->where('descripcion', 'gasolina para camioneta')
            ->exists();

            // Si existe al menos un producto de tipo "gasolina", aplica la validación a `km`
            if ($hasGasolina) {
                $rules['km'] = 'required|array'; // Asegura que km es un array
                $rules['km.*'] = 'numeric|min:0'; // Aplica las reglas necesarias para cada valor en km
            }
        }


        return $rules;
    }
}
