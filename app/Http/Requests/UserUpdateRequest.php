<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $rules = [
            'nombres' => 'required',
            'usuario' => 'required|unique:usuarios,usuario,' . $this->user->id,
            'perfil' => 'required',
            'activo' => 'required',
        ];

        if ($this->get('password'))
            $rules = array_merge($rules, ['password' => 'min:8', 'confirm_password' => 'required|same:password']);

        return $rules;
    }

    public function messages()
    {
        return [
            'nombres.required' => 'Ingrese el nombre.',
            'usuario.required' => 'Ingrese el usuario.',
            'usuario.unique' => 'El usuario ingresado ya esta en uso.',
            'password.required' => 'Ingrese una contraseña.',
            'password.min' => 'La contraseña debe contener mínimo de :min caracteres.',
            'confirm_password.required' => 'Confirme la contraseña',
            'confirm_password.same' => 'Las contraseñas no coinciden.',
            'perfil.required' => 'Seleccione un Perfil para el Usuario.',
            'activo.required' => 'Seleccione una opción.'
        ];
    }
}
