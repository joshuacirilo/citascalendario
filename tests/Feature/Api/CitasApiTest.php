<?php

namespace Tests\Feature\Api;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CitasApiTest extends TestCase
{
    private Paciente $paciente;

    private Paciente $otroPaciente;

    private Doctor $doctor;

    private Doctor $otroDoctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crearEsquema();

        $this->paciente = Paciente::create([
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'email' => 'juan@example.test',
        ]);

        $this->otroPaciente = Paciente::create([
            'nombres' => 'Maria',
            'apellidos' => 'Lopez',
            'email' => 'maria@example.test',
        ]);

        $this->doctor = Doctor::create([
            'nombres' => 'Andrea',
            'apellidos' => 'Garcia',
            'especialidad' => 'Medicina General',
            'email' => 'andrea@example.test',
        ]);

        $this->otroDoctor = Doctor::create([
            'nombres' => 'Luis',
            'apellidos' => 'Morales',
            'especialidad' => 'Cardiologia',
            'email' => 'luis@example.test',
        ]);
    }

    public function test_lista_pacientes_y_doctores(): void
    {
        $this->getJson('/api/pacientes')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'nombres', 'apellidos']]]);

        $this->getJson('/api/doctores')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'nombres', 'apellidos', 'especialidad']]]);
    }

    public function test_lista_y_muestra_el_detalle_de_una_cita(): void
    {
        $cita = $this->crearCita();

        $this->getJson('/api/citas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.paciente.id', $this->paciente->id)
            ->assertJsonPath('data.0.doctor.id', $this->doctor->id);

        $this->getJson("/api/citas/{$cita->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $cita->id)
            ->assertJsonPath('data.doctor.especialidad', 'Medicina General');
    }

    public function test_crea_una_cita_pendiente(): void
    {
        $payload = [
            'paciente_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'fecha_hora_inicio' => '2026-10-10 09:00:00',
            'fecha_hora_fin' => '2026-10-10 09:30:00',
            'motivo' => 'Consulta general',
            'estado' => 'atendida',
        ];

        $this->postJson('/api/citas', $payload)
            ->assertCreated()
            ->assertJsonPath('message', 'Cita creada correctamente')
            ->assertJsonPath('data.estado', 'pendiente');

        $this->assertDatabaseHas('citas', [
            'motivo' => 'Consulta general',
            'estado' => 'pendiente',
        ]);
    }

    public function test_rechaza_datos_invalidos_sin_persistirlos(): void
    {
        $this->postJson('/api/citas', [
            'paciente_id' => 999999,
            'doctor_id' => 999999,
            'fecha_hora_inicio' => '2026-10-10 10:00:00',
            'fecha_hora_fin' => '2026-10-10 09:00:00',
            'motivo' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'paciente_id',
                'doctor_id',
                'fecha_hora_fin',
                'motivo',
            ]);

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_actualiza_la_cita_sin_modificar_su_estado(): void
    {
        $cita = $this->crearCita(['estado' => 'confirmada']);

        $this->putJson("/api/citas/{$cita->id}", [
            'doctor_id' => $this->otroDoctor->id,
            'fecha_hora_inicio' => '2026-10-11 12:00:00',
            'fecha_hora_fin' => '2026-10-11 12:45:00',
            'motivo' => 'Control actualizado',
            'estado' => 'cancelada',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Cita actualizada correctamente')
            ->assertJsonPath('data.estado', 'confirmada')
            ->assertJsonPath('data.doctor.id', $this->otroDoctor->id);

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'motivo' => 'Control actualizado',
            'estado' => 'confirmada',
        ]);
    }

    public function test_cancela_una_cita_sin_eliminarla(): void
    {
        $cita = $this->crearCita();

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'cancelada'])
            ->assertOk()
            ->assertJsonPath('message', 'Estado actualizado correctamente')
            ->assertJsonPath('data.estado', 'cancelada');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'cancelada',
        ]);
        $this->assertDatabaseCount('citas', 1);
    }

    public function test_valida_el_estado(): void
    {
        $cita = $this->crearCita();

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'invalido'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_filtra_citas(): void
    {
        $this->crearCita();
        $this->crearCita([
            'paciente_id' => $this->otroPaciente->id,
            'doctor_id' => $this->otroDoctor->id,
            'fecha_hora_inicio' => '2026-10-06 10:00:00',
            'fecha_hora_fin' => '2026-10-06 10:30:00',
            'estado' => 'confirmada',
        ]);
        $this->crearCita([
            'paciente_id' => $this->otroPaciente->id,
            'fecha_hora_inicio' => '2026-11-01 11:00:00',
            'fecha_hora_fin' => '2026-11-01 11:30:00',
        ]);

        $this->getJson("/api/citas?doctor_id={$this->doctor->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson("/api/citas?paciente_id={$this->paciente->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/citas?estado=pendiente')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/citas?desde=2026-10-05&hasta=2026-10-06')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_devuelve_json_404_para_una_cita_inexistente(): void
    {
        $this->get('/api/citas/999999')
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['message']);
    }

    private function crearCita(array $attributes = []): Cita
    {
        return Cita::create(array_merge([
            'paciente_id' => $this->paciente->id,
            'doctor_id' => $this->doctor->id,
            'fecha_hora_inicio' => '2026-10-05 09:00:00',
            'fecha_hora_fin' => '2026-10-05 09:30:00',
            'motivo' => 'Consulta general',
            'estado' => 'pendiente',
        ], $attributes));
    }

    private function crearEsquema(): void
    {
        Schema::create('pacientes', function (Blueprint $table): void {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable()->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
        });

        Schema::create('doctores', function (Blueprint $table): void {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('especialidad', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->unique();
            $table->timestamps();
        });

        Schema::create('citas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->string('motivo', 255);
            $table->string('estado')->default('pendiente');
            $table->timestamps();
        });
    }
}
