<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropiedadesVentaUpdateRequest extends FormRequest
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
            'nombre' => 'required|unique:venta_propiedades,nombre,' . $this->propiedad->id,
            'direccion' => 'required',
            'area' => 'required|numeric',
            'telefono' => 'nullable|string|max:10',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'precio_venta' => 'required|numeric',
            'precio_mt2' => 'required|numeric',
            'estado' => 'required|in:DISPONIBLE,VENDIDO',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingrese el nombre.',
            'direccion.required' => 'Ingrese la dirección.',
            'area.required' => 'Ingrese el área.',
            'telefono.max' => 'El teléfono no puede exceder los 10 caracteres.',
            'latitud.required' => 'Ingrese la latitud.',
            'longitud.required' => 'Ingrese la longitud.',
            'precio_venta.required' => 'Ingrese el precio de venta.',
            'precio_mt2.required' => 'Ingrese el precio por metro cuadrado.',
            'estado.required' => 'Seleccione el estado de la propiedad.',
            'estado.in' => 'El estado debe ser uno de los siguientes: DISPONIBLE, VENDIDO.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // You can manipulate the request data before validation if needed
        $this->merge([
            'telefono' => $this->telefono ? preg_replace('/\D/', '', $this->telefono) : null, // Clean phone number
            'precio_venta' => $this->precio_venta ? str_replace(',', '', $this->precio_venta) : null,
            'precio_mt2' => $this->precio_mt2 ? str_replace(',', '', $this->precio_mt2) : null,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->latitud) || empty($this->longitud)) {
                $validator->errors()->add('ubicacion', 'Debe ingresar la ubicación (latitud y longitud).');
            }
        });
    }
}