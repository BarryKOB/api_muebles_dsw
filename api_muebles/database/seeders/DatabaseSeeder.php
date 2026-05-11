<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos a los seeders en orden (categorías primero, luego muebles)
        $this->call([
            CategoriaSeeder::class,
            MuebleSeeder::class,
        ]);
    }
}
