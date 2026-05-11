<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear categorías fijas con nombres reales de tienda de muebles
        $categorias = [
            ['nombre' => 'Sofás',           'descripcion' => 'Sofás y sillones para el salón'],
            ['nombre' => 'Mesas',           'descripcion' => 'Mesas de comedor, auxiliares y de centro'],
            ['nombre' => 'Sillas',          'descripcion' => 'Sillas de comedor, oficina y exteriores'],
            ['nombre' => 'Estanterías',     'descripcion' => 'Estanterías y librerías para organización'],
            ['nombre' => 'Camas',           'descripcion' => 'Camas individuales, dobles y abatibles'],
            ['nombre' => 'Armarios',        'descripcion' => 'Armarios y sistemas de almacenaje'],
            ['nombre' => 'Escritorios',     'descripcion' => 'Escritorios y mesas de trabajo'],
            ['nombre' => 'Cómodas',         'descripcion' => 'Cómodas y cajoneras para dormitorio'],
        ];

        foreach ($categorias as $cat) {
            Categoria::create($cat);
        }
    }
}
