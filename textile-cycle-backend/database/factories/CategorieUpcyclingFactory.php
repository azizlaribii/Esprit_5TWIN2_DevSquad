<?php

namespace Database\Factories;

use App\Models\CategorieUpcycling;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieUpcyclingFactory extends Factory
{
    protected $model = CategorieUpcycling::class;

    public function definition(): array
    {
        return [
            'nom'         => ucfirst($this->faker->unique()->word()),
            'description' => $this->faker->sentence(6),
            'icone'       => $this->faker->randomElement(['category', 'shopping_bag', 'weekend', 'palette', 'watch']),
            'actif'       => true,
        ];
    }
}
