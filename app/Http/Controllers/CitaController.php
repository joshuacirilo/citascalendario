<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCitaRequest;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaRequest;
use App\Http\Requests\UpdateEstadoCitaRequest;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;

class CitaController extends Controller
{
    public function __construct(private readonly CitaService $citaService) {}

    public function index(IndexCitaRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->citaService->listar($request->validated()),
        ]);
    }

    public function store(StoreCitaRequest $request): JsonResponse
    {
        $cita = $this->citaService->crear($request->validated());

        return response()->json([
            'message' => 'Cita creada correctamente',
            'data' => $cita,
        ], 201);
    }

    public function show(Cita $cita): JsonResponse
    {
        return response()->json([
            'data' => $this->citaService->detalle($cita),
        ]);
    }

    public function update(UpdateCitaRequest $request, Cita $cita): JsonResponse
    {
        $cita = $this->citaService->actualizar($cita, $request->validated());

        return response()->json([
            'message' => 'Cita actualizada correctamente',
            'data' => $cita,
        ]);
    }

    public function updateEstado(UpdateEstadoCitaRequest $request, Cita $cita): JsonResponse
    {
        $cita = $this->citaService->cambiarEstado(
            $cita,
            $request->validated()['estado'],
        );

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'data' => $cita,
        ]);
    }
}
