<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Producto;
use App\Models\CarritoItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompraTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmar_compra_funciona()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $producto = Producto::create([
            'nombre' => 'Manzana',
            'precio' => 1.50,
            'stock' => 10
        ]);

        CarritoItem::create([
            'user_id' => $user->id,
            'producto_id' => $producto->id,
            'cantidad' => 2
        ]);

        $response = $this->postJson('/api/confirmar-compra');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'order' => [
                'id',
                'total',
                'items' => [
                    ['producto_id', 'cantidad', 'precio_unitario']
                ]
            ]
        ]);
    }
}
