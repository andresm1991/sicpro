<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropiedadesVentaStoreRequest extends FormRequest
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
            'nombre' => 'required',
            'direccion' => 'required',
            'area' => 'required|numeric',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'precio_venta' => 'required|numeric',
            'precio_mt2' => 'required|numeric',
            'estado' => 'required|in:DISPONIBLE,VENDIDO',
            'tipo' => 'required',
            'files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Max 2MB for each image
            'files' => 'nullable|array|max:5', // Max 5 images
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingrese el nombre.',
            'direccion.required' => 'Ingrese la dirección.',
            'area.required' => 'Ingrese el área.',
            'latitud.required' => 'Ingrese la latitud.',
            'longitud.required' => 'Ingrese la longitud.',
            'precio_venta.required' => 'Ingrese el precio de venta.',
            'precio_mt2.required' => 'Ingrese el precio por metro cuadrado.',
            'estado.required' => 'Seleccione el estado de la propiedad.',
            'estado.in' => 'El estado debe ser uno de los siguientes: DISPONIBLE, VENDIDO.',
            'files.*.image' => 'Cada archivo debe ser una imagen válida.',
            'files.*.mimes' => 'Las imágenes deben ser de tipo: jpeg, png, jpg, gif, svg.',
            'files.*.max' => 'Cada imagen no puede exceder los 2MB.',
            'files.array' => 'Los archivos deben ser un arreglo de imágenes.',
            'files.max' => 'No se pueden subir más de 5 imágenes.',
            'tipo.required' => 'Seleccione el tipo de propiedad.',
        ];
    }
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // You can manipulate the request data before validation if needed
        $this->merge([
            // 'telefono' => $this->telefono ? preg_replace('/\D/', '', $this->telefono) : null, // Clean phone number
            'precio_venta' => $this->precio_venta ? str_replace(',', '', $this->precio_venta) : null,
            'precio_mt2' => $this->precio_mt2 ? str_replace(',', '', $this->precio_mt2) : null,
        ]);
    }
    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'Nombre de la propiedad',
            'direccion' => 'Dirección de la propiedad',
            'area' => 'Área de la propiedad',
            'telefono' => 'Teléfono de contacto',
            'latitud' => 'Latitud de la propiedad',
            'longitud' => 'Longitud de la propiedad',
            'precio_venta' => 'Precio de venta',
            'precio_mt2' => 'Precio por metro cuadrado',
            'estado' => 'Estado de la propiedad',
            'files.*' => 'Imágenes de la propiedad',
        ];
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