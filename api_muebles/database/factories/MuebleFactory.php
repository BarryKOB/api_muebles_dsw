<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mueble>
 */
class MuebleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombres = [
            'Sofá Chester', 'Mesa de comedor Oslo', 'Silla ergonómica Pro',
            'Estantería Kallax', 'Cama doble Malm', 'Armario PAX',
            'Escritorio Bekant', 'Cómoda Hemnes', 'Mesa de centro Lack',
            'Taburete alto Franklin', 'Sofá rinconera Kivik', 'Mesa extensible Bjursta',
            'Silla de oficina Markus', 'Estantería Billy', 'Cama individual Brimnes',
            'Armario Platsa', 'Escritorio Micke', 'Cómoda Tarva',
            'Mesa auxiliar Liatorp', 'Taburete bajo Nilsolle',
            'Sofá cama Friheten', 'Mesa redonda Docksta', 'Silla Tobias',
            'Estantería Eket', 'Cama abatible Nordli', 'Armario Brimnes',
            'Escritorio Alex', 'Cómoda Kullen', 'Mesa nido Vittsjö',
            'Taburete Dalfred', 'Sofá dos plazas Landskrona', 'Mesa alta Tornviken',
        ];

        $colores = ['Blanco', 'Negro', 'Roble', 'Nogal', 'Gris', 'Beige', 'Azul marino', 'Verde oliva', null];
        $materiales = ['Madera maciza', 'MDF', 'Contrachapado', 'Metal', 'Tela', 'Cuero', 'Cristal', 'Bambú', null];

        return [
            'nombre'       => $this->faker->randomElement($nombres) . ' ' . $this->faker->unique()->numberBetween(1, 999),
            'descripcion'  => $this->faker->paragraph(3),
            'precio'       => $this->faker->randomFloat(2, 29.99, 2499.99),
            'stock'        => $this->faker->numberBetween(0, 100),
            'color'        => $this->faker->randomElement($colores),
            'material'     => $this->faker->randomElement($materiales),
            'categoria_id' => Categoria::inRandomOrder()->first()?->id ?? Categoria::factory(),
            'imagen_url'   => $this->faker->imageUrl(640, 480, 'furniture', true),
        ];
    }
}
