<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Fuerza el uso del guard 'api'
            auth()->shouldUse('api');

            // Intenta autenticar el usuario con el token
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json(['message' => 'Usuario no encontrado.'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'Unauthenticated.', 'error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
