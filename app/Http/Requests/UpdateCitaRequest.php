<?php

namespace App\Http\Requests;

use App\Models\Cita;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cita = $this->route('cita');

        if (! $cita instanceof Cita) {
            return;
        }

        $dates = [];

        if (! $this->exists('fecha_hora_inicio')) {
            $dates['fecha_hora_inicio'] = $cita->fecha_hora_inicio->format('Y-m-d H:i:s');
        }

        if (! $this->exists('fecha_hora_fin')) {
            $dates['fecha_hora_fin'] = $cita->fecha_hora_fin->format('Y-m-d H:i:s');
        }

        $this->merge($dates);
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['sometimes', 'required', 'integer', 'exists:pacientes,id'],
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:doctores,id'],
            'fecha_hora_inicio' => ['required', 'date_format:Y-m-d H:i:s'],
            'fecha_hora_fin' => ['required', 'date_format:Y-m-d H:i:s', 'after:fecha_hora_inicio'],
            'motivo' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
