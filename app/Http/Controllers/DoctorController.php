<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function index(): JsonResponse
    {
        $doctores = Doctor::query()
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'especialidad']);

        return response()->json(['data' => $doctores]);
    }
}
