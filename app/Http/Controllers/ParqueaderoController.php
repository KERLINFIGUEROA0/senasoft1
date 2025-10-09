<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Registro;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ParqueaderoController extends Controller
{
    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:20',
            'nombre' => 'required|string|max:150',
            'placa' => 'required|string|max:10',
            'marca' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'tipo' => 'required|in:Motocicleta,Carro,Camion,',
        ]);

        $registroActivo = Registro::where('vehiculo_placa', strtoupper($request->placa))->where('estado', 'Activo')->first();
        if ($registroActivo) {
            return response()->json(['message' => 'Este vehiculo ya existe en el parqueadero.'], 409);
        }

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

        $espacioAsignado = $this->encontrarEspacioDisponible();
        if (!$espacioAsignado) {
            return response()->json(['message' => 'El parqueadero esta full.'], 400);
        }

        $registro = Registro::create([
            'cliente_id' => $cliente->id,
            'vehiculo_placa' => $vehiculo->placa,
            'hora_ingreso' => Carbon::now(),
            'piso' => $espacioAsignado['piso'],
            'espacio' => $espacioAsignado['espacio'],
            'estado' => 'Activo',
        ]);

        return response()->json([
            'message' => 'Vehículo registrado correctamente.',
            'registro' => $registro
        ], 201);
    }

    public function registrarSalida(Request $request)
    {
        $request->validate(['placa' => 'required|string|exists:vehiculos,placa']);

        $registro = Registro::where('vehiculo_placa', strtoupper($request->placa))
            ->where('estado', 'Activo')
            ->first();

        if (!$registro) {
            return response()->json(['message' => 'No se encontro un registro de salida para este vehiculo.'], 404);
        }

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

        return null;
    }

    public function estadoActual()
    {
        $totalPisos = 4;
        $espaciosPorPiso = 10;

        $registrosActivos = Registro::where('estado', 'Activo')->get();

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

    public function calcularGananciasTotales()
    {
        Log::info('Método calcularGananciasTotales llamado');
        $ganancias = Registro::where('estado', 'Finalizado')->sum('total_pagado');

        return response()->json([
            'mensaje' => 'El total de ganancias hasta ahora es:',
            'total_ganado' => '$' . number_format($ganancias, 2) . ' USD'
        ]);
    }
}
