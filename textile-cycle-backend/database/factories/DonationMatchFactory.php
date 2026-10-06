<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\Donation;
use App\Models\DonationMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DonationMatch> */
class DonationMatchFactory extends Factory
{
    protected $model = DonationMatch::class;

    public function definition(): array
    {
        return [
            'donation_id'         => Donation::factory(),
            'association_id'      => Association::factory(),
            'association_need_id' => null,
            'score'               => fake()->randomFloat(1, 40, 98),
            'reasons'             => ['Accepte cette catégorie de vêtements', 'À ' . fake()->numberBetween(1, 30) . ' km'],
            'explanation'         => null,
            'quantity'            => 1,
            'status'              => DonationMatch::SUGGESTED,
        ];
    }

    public function requested(): static
    {
        return $this->state(['status' => DonationMatch::REQUESTED]);
    }

    public function accepted(): static
    {
        return $this->state([
            'status'       => DonationMatch::ACCEPTED,
            'meeting_type' => 'depot',
            'meeting_at'   => now()->addDays(3),
            'meeting_note' => 'Entrée principale, demander l\'accueil.',
            'responded_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status'       => DonationMatch::COMPLETED,
            'meeting_type' => 'depot',
            'meeting_at'   => now()->subDays(2),
            'responded_at' => now()->subDays(4),
        ]);
    }
}
