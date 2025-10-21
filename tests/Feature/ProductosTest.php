<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductosTest extends TestCase
{
    /**
     * A basic feature test example.
     */
   public function test_ruta_productos_responde_correctamente()
{
    $response = $this->get('/api/productos');

    $response->assertStatus(200);
}

}
