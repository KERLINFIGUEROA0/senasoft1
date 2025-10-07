<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Registro;
use Carbon\Carbon;

class ParqueaderoController extends Controller
{
    // Método para registrar la entrada de un vehículo
    public function registrarEntrada(Request $request)
    {
        // 1. Validar los datos de entrada
        $request->validate([
            'documento' => 'required|string|max:20',
            'nombre' => 'required|string|max:150',
            'placa' => 'required|string|max:10',
            'marca' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'tipo' => 'required|in:Motocicleta,Carro',
        ]);

        // Verificar si el vehículo ya está adentro
        $registroActivo = Registro::where('vehiculo_placa', strtoupper($request->placa))->where('estado', 'Activo')->first();
        if ($registroActivo) {
            return response()->json(['message' => 'Este vehículo ya se encuentra en el parqueadero.'], 409);
        }

        // 2. Crear o encontrar el cliente y el vehículo (para no duplicar)
        $cliente = Cliente::firstOrCreate(
            ['documento' => $request->documento],
            ['nombre' => $request->nombre]
        );

        $vehiculo = Vehiculo::firstOrCreate(
            ['placa' => strtoupper($request->placa)],
            [
                'marca' => $request->marca,
                'color' => $request->color,
                'tipo' => $request->tipo,
            ]
        );

        // 3. Lógica para encontrar el próximo espacio disponible
        $espacioAsignado = $this->encontrarEspacioDisponible();
        if (!$espacioAsignado) {
            return response()->json(['message' => 'El parqueadero está lleno.'], 400);
        }

        // 4. Crear el registro de entrada
        $registro = Registro::create([
            'cliente_id' => $cliente->id,
            'vehiculo_placa' => $vehiculo->placa,
            'hora_ingreso' => Carbon::now(),
            'piso' => $espacioAsignado['piso'],
            'espacio' => $espacioAsignado['espacio'],
            'estado' => 'Activo',
        ]);

        return response()->json([
            'message' => 'Vehículo registrado con éxito.',
            'registro' => $registro
        ], 201);
    }

    // Método para registrar la salida y calcular el costo
    public function registrarSalida(Request $request)
    {
        $request->validate(['placa' => 'required|string|exists:vehiculos,placa']);

        $registro = Registro::where('vehiculo_placa', strtoupper($request->placa))
                              ->where('estado', 'Activo')
                              ->first();

        if (!$registro) {
            return response()->json(['message' => 'No se encontró un registro activo para esta placa.'], 404);
        }

        // Calcular costo
        $horaIngreso = new Carbon($registro->hora_ingreso);
        $horaSalida = Carbon::now();
        
        $minutosTranscurridos = $horaIngreso->diffInMinutes($horaSalida);
        if ($minutosTranscurridos == 0) {
            $horasTranscurridas = 1;
        } else {
            $horasTranscurridas = ceil($minutosTranscurridos / 60);
        }


        $tipoVehiculo = $registro->vehiculo->tipo;
        $tarifa = ($tipoVehiculo == 'Carro') ? 3.0 : 1.5;
        $totalPagar = $horasTranscurridas * $tarifa;

        // Actualizar el registro
        $registro->update([
            'hora_salida' => $horaSalida,
            'total_pagado' => $totalPagar,
            'estado' => 'Finalizado'
        ]);

        return response()->json([
            'message' => 'Salida registrada con éxito.',
            'placa' => $registro->vehiculo_placa,
            'tiempo_total_horas' => $horasTranscurridas,
            'total_a_pagar' => '$' . number_format($totalPagar, 2) . ' USD',
        ]);
    }

    // Lógica para encontrar el espacio
    private function encontrarEspacioDisponible()
    {
        $espaciosOcupados = Registro::where('estado', 'Activo')
                                      ->select('piso', 'espacio')
                                      ->get()
                                      ->map(function ($item) {
                                          return "{$item->piso}-{$item->espacio}";
                                      })->toArray();

        for ($piso = 1; $piso <= 4; $piso++) {
            for ($espacio = 1; $espacio <= 10; $espacio++) {
                if (!in_array("{$piso}-{$espacio}", $espaciosOcupados)) {
                    return ['piso' => $piso, 'espacio' => $espacio];
                }
            }
        }

        return null; // Parqueadero lleno
    }
    
    /**
     * Devuelve el estado actual de todos los espacios del parqueadero.
     */
    public function estadoActual()
    {
        $totalPisos = 4;
        $espaciosPorPiso = 10;
        
        $registrosActivos = Registro::where('estado', 'Activo')->get();

        // Creamos un mapa de búsqueda para eficiencia
        $mapaOcupados = [];
        foreach ($registrosActivos as $registro) {
            $mapaOcupados["{$registro->piso}-{$registro->espacio}"] = $registro->vehiculo_placa;
        }

        $estadoParqueadero = [];

        for ($piso = 1; $piso <= $totalPisos; $piso++) {
            $espacios = [];
            for ($espacio = 1; $espacio <= $espaciosPorPiso; $espacio++) {
                $key = "{$piso}-{$espacio}";
                
                if (isset($mapaOcupados[$key])) {
                    $espacios[] = [
                        'espacio_nro' => $espacio,
                        'estado' => 'Ocupado',
                        'placa' => $mapaOcupados[$key]
                    ];
                } else {
                    $espacios[] = [
                        'espacio_nro' => $espacio,
                        'estado' => 'Libre',
                        'placa' => null
                    ];
                }
            }
            
            $estadoParqueadero[] = [
                'piso_nro' => $piso,
                'espacios' => $espacios
            ];
        }

        return response()->json($estadoParqueadero);
    }
}

