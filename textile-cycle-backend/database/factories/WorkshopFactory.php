<?php

namespace Database\Factories;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour l'entité Workshop (Atelier de réparation)
 *
 * Relation Eloquent : Workshop hasMany RepairRequest (1-N)
 */
class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition(): array
    {
        $specialtiesPool = [
            'Retouche', 'Broderie', 'Cuir', 'Denim',
            'Déchirures', 'Fermetures éclair', 'Teinture', 'Raccommodage',
        ];

        return [
            'name'        => 'Atelier ' . $this->faker->lastName(),
            'address'     => $this->faker->streetAddress(),
            'city'        => $this->faker->city(),
            'latitude'    => $this->faker->latitude(30.0, 50.0),
            'longitude'   => $this->faker->longitude(-5.0, 15.0),
            'phone'       => $this->faker->phoneNumber(),
            'rating'      => $this->faker->randomFloat(1, 3.0, 5.0),
            'specialties' => $this->faker->randomElements($specialtiesPool, 3),
        ];
    }

    /** État : atelier très bien noté */
    public function topRated(): static
    {
        return $this->state(fn () => ['rating' => $this->faker->randomFloat(1, 4.5, 5.0)]);
    }
}
