<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParqueaderoController;

Route::post('/parqueadero/entrada', [ParqueaderoController::class, 'registrarEntrada']);

Route::post('/parqueadero/salida', [ParqueaderoController::class, 'registrarSalida']);

Route::get('/parqueadero/estado', [ParqueaderoController::class, 'estadoActual']);

Route::get('/parqueadero/ganancias', [ParqueaderoController::class, 'calcularGananciasTotales']);
