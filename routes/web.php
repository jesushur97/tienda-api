<?php

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->stateless()->redirect();
});

Route::get('/auth/callback', function () {
    $googleUser = Socialite::driver('google')->stateless()->user();




    // Buscar o crear usuario en la base de datos
    $user = User::updateOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'name' => $googleUser->getName(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
             'password' => bcrypt(Str::random(16)) // contraseña aleatoria para cumplir con la DB
        ]
    );

    // Autenticar al usuario y generar token JWT
    Auth::login($user);
    $token = JWTAuth::fromUser($user);

    // Devolver el token como JSON
    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
});
