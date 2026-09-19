<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\JsonResponse;

class PacienteController extends Controller
{
    public function index(): JsonResponse
    {
        $pacientes = Paciente::query()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos']);

        return response()->json(['data' => $pacientes]);
    }
}
