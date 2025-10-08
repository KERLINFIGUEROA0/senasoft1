<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error('Excepción no manejada: ' . $e->getMessage(), ['exception' => $e]);
        });

        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            Log::info('AuthenticationException capturada: ' . $e->getMessage());
            return response()->json(['error' => 'Token no válido'], 401);
        });

        $this->renderable(function (TokenExpiredException $e, $request) {
            return response()->json(['error' => 'Token expirado'], 401);
        });

        $this->renderable(function (TokenInvalidException $e, $request) {
            return response()->json(['error' => 'Token inválido'], 401);
        });

        $this->renderable(function (JWTException $e, $request) {
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        });
    }
}
