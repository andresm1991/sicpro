<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ValidarFechasPlanificacionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cambia según tus necesidades de autorización
    }

    public function rules()
    {
        return [
            'fecha_inicio' => ['required', 'date', function ($attribute, $value, $fail) {
                $fechaInicio = Carbon::parse($value);
                $diaSemana = $fechaInicio->dayOfWeek;

                // Validar que la fecha de inicio no sea sábado ni domingo
                if ($diaSemana === 0 || $diaSemana === 6) {
                    $fail("La fecha de inicio debe ser un lunes o cualquier día hábil de la semana.");
                }
            }],
            'fecha_fin' => ['required', 'date', function ($attribute, $value, $fail) {
                $fechaFin = Carbon::parse($value);
                $diaSemana = $fechaFin->dayOfWeek;

                // Validar que la fecha de fin no sea domingo
                if ($diaSemana === 0) {
                    $fail("La fecha de fin no puede ser un domingo.");
                }
            }],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fechaInicio = Carbon::parse($this->input('fecha_inicio'));
            $fechaFin = Carbon::parse($this->input('fecha_fin'));

            // Validar que la fecha de inicio no sea mayor a la fecha de fin
            if ($fechaInicio->greaterThan($fechaFin)) {
                $validator->errors()->add('fecha_inicio', 'La fecha de inicio no puede ser mayor a la fecha de fin.');
            }

            // Validar que la fecha de fin no sea menor a la fecha de inicio
            if ($fechaFin->lessThan($fechaInicio)) {
                $validator->errors()->add('fecha_fin', 'La fecha de fin no puede ser menor a la fecha de inicio.');
            }

            // Validar que el rango no sea mayor a 6 días
            if ($fechaInicio->diffInDays($fechaFin) > 6) {
                $validator->errors()->add('fecha_fin', 'El rango entre las fechas debe ser como máximo de 6 días.');
            }
        });
    }

    public function messages()
    {
        return [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
        ];
    }
}