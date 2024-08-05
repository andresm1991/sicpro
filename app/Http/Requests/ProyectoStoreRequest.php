<?php

namespace App\Http\Requests;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nombre_proyecto' => 'required|unique:proyectos',
            'propietario' => 'required',
            'ubicacion_proyecto' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'correo' => 'required|email',
            'tipo_proyecto' => 'required',
            'area_lote' => 'required',
            'area_construccion' => 'required',
            'presupuesto' => 'required',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'portada' => 'required|file|mimes:jpg,png,jpeg|max:5120',
        ];
        if ($this->get('archivos')) {
            $rules = array_merge($rules, ['archivos.*' => 'file']);
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'nombre_proyecto.required' => 'Ingrese el nombre del proyecto.',
            'nombre_proyecto.unique' => 'El nombre del proyecto ya se encuentra registrado.',
            'propietario.required' => 'Ingrese el nombre del propietrario.',
            'ubicacion_proyecto.required' => 'Ingrese la ubicacion geográfica del proyecto.',
            'direccion.required' => 'Ingresa la dirección del proyecto.',
            'telefono.required' => 'Ingrese un número telefónico.',
            'correo.required' => 'Ingrese una dirección de correo.',
            'correo.email' => 'Ingrese una dirección de correo válida.',
            'tipo_proyecto.required' => 'Seleccione el tipo de proyecto.',
            'area_lote.required' => 'Ingrese el area del lote.',
            'area_construccion.required' => 'Ingrese el area de construcción.',
            'presupuesto.required' => 'Ingrese el presupuesto.',
            'fecha_inicio.required' => 'Seleccione la fecha de inicio.',
            'fecha_fin.required' => 'Seleccione la fecha de finalización.',
            'portada.required' => 'Selecione una imagen para la portada del proyecto.',
            'portada.file' => 'El archivo debe ser un archivo válido.',
            'portada.mimes' => 'Solo se permiten archivos de tipo, :values',
            'portada.max' => 'El archivo no debe exceder los 5 MB.',
            'archivos.*.file' => 'Cada archivo debe ser un archivo válido.',
        ];
    }
}
