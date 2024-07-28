<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PerfilUserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        if ($this->get('password') || $this->get('password_confirmation'))
            $rules = array_merge($rules, ['password' => 'required|min:8', 'password_confirmation' => 'required|same:password',]);

        if ($this->get('telefono'))
            $rules = array_merge($rules, ['telefono' => 'numeric']);

        if ($this->get('correo'))
            $rules = array_merge($rules, ['correo' => 'email|unique:usuarios,correo,' . $this->user->id]);

        return $rules;
    }

    function messages()
    {
        return [
            'password.required' => 'Ingrese la nueva contraseña.',
            'password.min' => 'La contraseña debe contener al menos 8 caracteres.',
            'password_confirmation.required' => 'Confirme la contraseña.',
            'password_confirmation.same' => 'La contraseña no coincide.',
            'telefono.numeric' => 'El teléfono debe ser numérico.',
            'correo.email' => 'La dirección de correo debe ser una dirección válida.'
        ];
    }
}
