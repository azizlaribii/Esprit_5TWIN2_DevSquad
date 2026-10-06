<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Depot>
 */
class DepotFactory extends Factory
{

public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'categorie'   => fake()->randomElement([
                'T-Shirts & Tops', 'Pantalons & Jeans', 'Vestes & Manteaux',
                'Robes & Jupes', 'Chaussures', 'Sportswear',
            ]),
            'quantite'    => fake()->numberBetween(1, 10),
            'etat'        => fake()->randomElement(['Très bon état', 'Bon état', 'A réparer', 'Usé']),
            'statut'      => fake()->randomElement(['en_attente', 'valide', 'traite']),
            'description' => fake()->sentence(10),
            'photo'       => null,
        ];
    }
}
