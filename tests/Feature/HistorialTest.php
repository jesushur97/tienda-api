<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_ver_su_historial_de_compras()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $producto = Producto::create([
            'nombre' => 'Mochila',
            'precio' => 29.99,
            'stock' => 10,
        ]);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/carrito', [
                 'producto_id' => $producto->id,
                 'cantidad' => 1,
             ]);

        $this->withHeader('Authorization', "Bearer $token")
             ->post('/api/confirmar-compra');

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->get('/api/mis-compras');

        $response->assertStatus(200);
        $response->assertJsonFragment(['total' => '29.99']);
    }
}
