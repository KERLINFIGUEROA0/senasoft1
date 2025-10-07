<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParqueaderoController;

// Endpoint para registrar la entrada de un vehículo
Route::post('/parqueadero/entrada', [ParqueaderoController::class, 'registrarEntrada']);

// Endpoint para registrar la salida de un vehículo
Route::post('/parqueadero/salida', [ParqueaderoController::class, 'registrarSalida']);
// NUEVO: Endpoint para ver el estado completo del parqueadero
Route::get('/parqueadero/estado', [ParqueaderoController::class, 'estadoActual']);
