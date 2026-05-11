<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mueble;

class MuebleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 40 muebles con datos aleatorios usando la factory
        // (las categorías ya deben existir gracias al CategoriaSeeder)
        Mueble::factory()->count(40)->create();
    }
}
