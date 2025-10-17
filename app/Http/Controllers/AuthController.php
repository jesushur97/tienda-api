<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Iniciar sesión y devolver token JWT
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json(['token' => $token]);
    }

    // Obtener usuario autenticado
    public function me()
    {
        return response()->json(Auth::user());
    }

    // Cerrar sesión (invalidar token)
    public function logout()
    {
        Auth::logout();
        return response()->json(['mensaje' => 'Sesión cerrada']);
    }
}
