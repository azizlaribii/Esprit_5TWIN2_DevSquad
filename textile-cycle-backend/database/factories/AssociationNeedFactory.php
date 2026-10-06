<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\AssociationNeed;
use App\Support\Textile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AssociationNeed> */
class AssociationNeedFactory extends Factory
{
    protected $model = AssociationNeed::class;

    public function definition(): array
    {
        return [
            'association_id'  => Association::factory(),
            'category'        => fake()->randomElement(array_keys(Textile::CATEGORIES)),
            'age_group'       => fake()->randomElement([null, ...array_keys(Textile::AGE_GROUPS)]),
            'size'            => null,
            'gender'          => 'mixte',
            'season'          => fake()->randomElement(array_keys(Textile::SEASONS)),
            'quantity_needed' => fake()->numberBetween(5, 50),
            'urgency'         => fake()->numberBetween(1, 5),
            'expires_at'      => now()->addMonths(2)->toDateString(),
        ];
    }

    public function urgent(): static
    {
        return $this->state(['urgency' => 5]);
    }
}
