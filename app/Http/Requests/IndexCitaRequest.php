<?php

namespace App\Http\Requests;

use App\Models\Cita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hastaRules = ['sometimes', 'date_format:Y-m-d'];

        if ($this->filled('desde')) {
            $hastaRules[] = 'after_or_equal:desde';
        }

        return [
            'doctor_id' => ['sometimes', 'integer', 'exists:doctores,id'],
            'paciente_id' => ['sometimes', 'integer', 'exists:pacientes,id'],
            'desde' => ['sometimes', 'date_format:Y-m-d'],
            'hasta' => $hastaRules,
            'estado' => ['sometimes', Rule::in(Cita::ESTADOS)],
        ];
    }
}
