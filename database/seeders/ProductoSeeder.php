<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Producto::create(['nombre' => 'Manzana', 'precio' => 1.50, 'stock' =>100]);
        Producto::create(['nombre' => 'Pan', 'precio' => 0.80, 'stock' => 50]);
        Producto::create(['nombre' => 'Leche', 'precio' => 1.20, 'stock' => 30]);
    }
}
