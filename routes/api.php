<?php

use App\Http\Controllers\CitaController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PacienteController;
use Illuminate\Support\Facades\Route;

Route::get('/pacientes', [PacienteController::class, 'index']);
Route::get('/doctores', [DoctorController::class, 'index']);

Route::get('/citas', [CitaController::class, 'index']);
Route::post('/citas', [CitaController::class, 'store']);
Route::get('/citas/{cita}', [CitaController::class, 'show']);
Route::put('/citas/{cita}', [CitaController::class, 'update']);
Route::patch('/citas/{cita}/estado', [CitaController::class, 'updateEstado']);
