<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctores,id'],
            'fecha_hora_inicio' => ['required', 'date_format:Y-m-d H:i:s'],
            'fecha_hora_fin' => ['required', 'date_format:Y-m-d H:i:s', 'after:fecha_hora_inicio'],
            'motivo' => ['required', 'string', 'max:255'],
        ];
    }
}
