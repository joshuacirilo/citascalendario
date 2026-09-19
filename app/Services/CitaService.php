<?php

namespace App\Services;

use App\Models\Cita;
use Illuminate\Database\Eloquent\Collection;

class CitaService
{
    private const RELATIONS = [
        'paciente:id,nombres,apellidos',
        'doctor:id,nombres,apellidos,especialidad',
    ];

    public function listar(array $filters): Collection
    {
        return Cita::query()
            ->with(self::RELATIONS)
            ->when(
                $filters['doctor_id'] ?? null,
                fn ($query, $doctorId) => $query->where('doctor_id', $doctorId),
            )
            ->when(
                $filters['paciente_id'] ?? null,
                fn ($query, $pacienteId) => $query->where('paciente_id', $pacienteId),
            )
            ->when(
                $filters['estado'] ?? null,
                fn ($query, $estado) => $query->where('estado', $estado),
            )
            ->when(
                $filters['desde'] ?? null,
                fn ($query, $desde) => $query->whereDate('fecha_hora_fin', '>=', $desde),
            )
            ->when(
                $filters['hasta'] ?? null,
                fn ($query, $hasta) => $query->whereDate('fecha_hora_inicio', '<=', $hasta),
            )
            ->orderBy('fecha_hora_inicio')
            ->get();
    }

    public function detalle(Cita $cita): Cita
    {
        return $cita->loadMissing(self::RELATIONS);
    }

    public function crear(array $data): Cita
    {
        $data['estado'] = 'pendiente';

        return $this->detalle(Cita::create($data));
    }

    public function actualizar(Cita $cita, array $data): Cita
    {
        $cita->update($data);

        return $this->detalle($cita->refresh());
    }

    public function cambiarEstado(Cita $cita, string $estado): Cita
    {
        $cita->update(['estado' => $estado]);

        return $this->detalle($cita->refresh());
    }
}
