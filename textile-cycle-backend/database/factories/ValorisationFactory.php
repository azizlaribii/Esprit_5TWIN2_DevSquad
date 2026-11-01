<?php

namespace Database\Factories;

use App\Models\Depot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValorisationFactory extends Factory
{
    public function definition(): array
    {
        $filiere = fake()->randomElement(['revente', 'reparation', 'don', 'recyclage']);

        return [
            'depot_id'        => Depot::factory(),
            'filiere'         => $filiere,
            'statut'          => fake()->randomElement(['propose', 'validee', 'realisee']),
            'estimation_prix' => $filiere === 'revente' ? fake()->randomFloat(2, 5, 80) : null,
            'commentaire'     => fake()->optional()->sentence(),
            'source'          => fake()->randomElement(['ia', 'manuelle']),
        ];
    }
}