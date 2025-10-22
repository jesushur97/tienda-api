<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Iniciar sesión y devolver token JWT
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }

    // Obtener usuario autenticado
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        }
    }

    // Cerrar sesión (invalidar token)
    public function logout()
    {
        try {
            JWTAuth::parseToken()->invalidate();
            return response()->json(['mensaje' => 'Sesión cerrada']);
        } catch (\Exception $e) {
           // return response()->json(['error' => 'Token inválido o no proporcionado'], 401);
        }
        return response()->json(['mensaje' => 'Sesión cerrada']);
    }
}
