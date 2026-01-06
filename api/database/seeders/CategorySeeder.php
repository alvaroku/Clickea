<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Salud',
            'Belleza',
            'Bienestar',
            'Deportes',
            'Educación',
            'Hogar',
            'Mantenimiento',
            'Otros'
        ];

        foreach ($categories as $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Servicios relacionados con {$name}",
                'active' => true,
            ]);
        }
    }
}
