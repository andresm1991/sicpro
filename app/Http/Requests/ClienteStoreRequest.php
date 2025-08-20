<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteStoreRequest extends FormRequest
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
            'nombres' => 'required|unique:clientes,nombre',
            'telefono' => 'nullable|numeric|max:10',
            'email' => 'nullable|email|max:100',
            'ciudad' => 'nullable|string|max:100',
            'ruc' => 'nullable|numeric|max:13',
            'contacto' => 'max:250',
            'telefono_contacto' => 'nullable|numeric|max:10',
            'email_contacto' => 'nullable|email|max:100',
            'observaciones' => 'nullable|string|max:250'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre del cliente es obligatorio.',
            'nombres.unique' => 'El nombre del cliente ya se encuentra registrado.',
            'telefono.numeric' => 'El teléfono debe ser solo numeros.',
            'telefono.max' => 'El teléfono no puede exceder los 10 dígitos.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'email.max' => 'El correo electrónico no puede exceder los 100 caracteres.',
            'ciudad.string' => 'La ciudad debe ser una cadena de texto.',
            'ciudad.max' => 'La ciudad no puede exceder los 100 caracteres.',
            'ruc.numeric' => 'El RUC debe ser solo números.',
            'ruc.max' => 'El RUC no puede exceder los 13 digitos.',
            'contacto.max' => 'El nombre de contacto no puede exceder los 100 caracteres.',
            'telefono_contacto.numeric' => 'El teléfono de contacto debe ser solo numeros.',
            'telefono_contacto.max' => 'El teléfono de contacto no puede exceder los 13 digitos.',
            'email_contacto.email' => 'El correo de contacto debe ser una dirección de correo válida.',
            'email_contacto.max' => 'El correo de contacto no puede exceder los 100 caracteres.',
            'observaciones.max' => 'Las observaciones no pueden exceder los 250 caracteres.'
        ];
    }
}