<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre'      => $this->faker->unique()->randomElement([
                'Sofás', 'Mesas', 'Sillas', 'Estanterías', 'Camas',
                'Armarios', 'Escritorios', 'Cómodas', 'Mesas de centro', 'Taburetes'
            ]),
            'descripcion' => $this->faker->sentence(10),
        ];
    }
}
