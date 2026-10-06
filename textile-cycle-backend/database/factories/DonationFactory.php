<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\DonationPhoto;
use App\Support\Textile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Don de démonstration. Le donateur est créé à la volée avec le rôle « user ».
 *
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        [$city, $lat, $lng] = AssociationFactory::randomCity();

        return [
            'user_id'     => fn () => AssociationFactory::createUser('user'),
            'title'       => fake()->randomElement([
                'Manteau d\'hiver', 'Lot de pulls', 'Robes d\'été', 'Pantalons en bon état', 'Vêtements de bébé',
            ]),
            'description' => fake()->sentence(10),
            'category'    => fake()->randomElement(array_keys(Textile::CATEGORIES)),
            'age_group'   => fake()->randomElement(array_keys(Textile::AGE_GROUPS)),
            'size'        => fake()->randomElement(['S', 'M', 'L', '5-6a', '7-8a']),
            'gender'      => fake()->randomElement(array_keys(Textile::GENDERS)),
            'season'      => fake()->randomElement(array_keys(Textile::SEASONS)),
            'condition'   => fake()->randomElement(['neuf', 'bon', 'usage']),
            'quantity'    => fake()->numberBetween(1, 8),
            'city'        => $city,
            'lat'         => $lat,
            'lng'         => $lng,
            'status'      => Donation::MATCHED,
        ];
    }

    /** Don tout juste déposé : l'analyse et le matching n'ont pas encore tourné. */
    public function pendingAnalysis(): static
    {
        return $this->state(['status' => Donation::PENDING_ANALYSIS]);
    }

    /** Catégorie et état inconnus : le donateur doit compléter la fiche. */
    public function needsReview(): static
    {
        return $this->state(['status' => Donation::NEEDS_REVIEW, 'category' => null, 'condition' => null]);
    }

    public function requested(): static
    {
        return $this->state(['status' => Donation::REQUESTED]);
    }

    public function completed(): static
    {
        return $this->state(['status' => Donation::COMPLETED]);
    }

    /** Ajoute des photos (relation hasMany « photos »). */
    public function withPhotos(int $count = 1): static
    {
        return $this->has(DonationPhoto::factory()->count($count), 'photos');
    }
}
