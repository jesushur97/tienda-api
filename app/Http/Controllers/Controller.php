<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function store(Request $request)
{
    $data = $request->json()->all();

    // Acceder a valores individuales
    $nombre = $data['firstName'];
    $empresa = $data['worksWith'][0];
    $mascota = $data['pets'][0]['name'];

    return response()->json([
        'mensaje' => "Datos recibidos correctamente",
        'nombre' => $nombre,
        'empresa' => $empresa,
        'mascota' => $mascota
    ]);
}
}
