<?php
use App\Models\User;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarritoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_ver_su_carrito()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // ← usa el guard correcto

        $producto = Producto::create([
            'nombre' => 'Camiseta',
            'precio' => 19.99,
            'stock' => 10,
        ]);

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/carrito', [
                 'producto_id' => $producto->id,
                 'cantidad' => 2,
             ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
                         ->get('/api/carrito');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);
    }
}
