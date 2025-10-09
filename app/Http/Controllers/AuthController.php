<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validar->fails()) {
            return response()->json($validar->errors(), 422);
        }

        if (! $token = auth('api')->attempt($validar->validated())) {
            return response()->json(['error' => 'Este usuario no esta autorizado.'],(401));
        }

        return $this->creartoken($token);
    }



    protected function creartoken($token)
    {
        return response()->json([
            'token' => $token,
            'expira en' => config('jwt.ttl') * 120 // Tiempo de vida del token en segundos
        ]);
    }
}
