<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParqueaderoController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación para la App Cliente
|--------------------------------------------------------------------------
*/
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rutas Protegidas del Parqueadero
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/parqueadero/entrada', [ParqueaderoController::class, 'registrarEntrada']);
    Route::post('/parqueadero/salida', [ParqueaderoController::class, 'registrarSalida']);
    Route::get('/parqueadero/estado', [ParqueaderoController::class, 'estadoActual']);
    Route::get('/parqueadero/ganancias', [ParqueaderoController::class, 'calcularGananciasTotales']);
});