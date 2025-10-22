<?php

namespace Tests\Feature;

use App\Models\User;

use Tests\TestCase;

class InicioTest extends TestCase
{

    public function test_usuario_puede_iniciar_sesion()
    {
        $user = User::factory()->create([
            'email' => 'prueba@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'prueba@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
    }

    public function test_login_falla_con_credenciales_invalidas()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'noexiste@example.com',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Credenciales inválidas',
        ]);
    }
}
