<?php
// Indica que este archivo es PHP

namespace App\Http\Controllers;
// Define en qué parte del proyecto está este archivo (ubicación lógica)

use Illuminate\Http\Request;
// Importa la clase Request, que representa los datos que llegan desde el navegador (como email y contraseña)

use Illuminate\Support\Facades\Auth;
// Importa el sistema de autenticación de Laravel (para iniciar/cerrar sesión, etc.)

use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
// Define una clase llamada AuthController que hereda de Controller (es un controlador)
{
    // Iniciar sesión y devolver token JWT
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (! $token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'Credenciales inválidas'], 401);
    }

    return response()->json(['token' => $token]);
}


    // Obtener usuario autenticado
    public function me()
    // Función pública llamada me (para saber quién está logueado)
    {
        return response()->json(Auth::user());
        // Devuelve en JSON los datos del usuario que está autenticado
    }

    // Cerrar sesión (invalidar token)
    public function logout()
    // Función pública llamada logout (cerrar sesión)
    {
        Auth::logout();
        // Cierra la sesión del usuario (invalida el token)

        return response()->json(['mensaje' => 'Sesión cerrada']);
        // Devuelve un mensaje en JSON diciendo que la sesión se cerró
    }
}
