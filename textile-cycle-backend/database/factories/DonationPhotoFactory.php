<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\DonationPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/** @extends Factory<DonationPhoto> */
class DonationPhotoFactory extends Factory
{
    protected $model = DonationPhoto::class;

    private const PLACEHOLDER = 'placeholders/vetement.svg';

    public function definition(): array
    {
        return [
            'donation_id' => Donation::factory(),
            'path'        => self::PLACEHOLDER,
        ];
    }

    /**
     * Écrit une illustration de remplacement (silhouette de vêtement) dans le disque « public »
     * pour que les photos de démonstration s'affichent. À appeler depuis le seeder.
     */
    public static function ensurePlaceholder(): void
    {
        $disk = Storage::disk('public');

        if (! $disk->exists(self::PLACEHOLDER)) {
            $disk->put(self::PLACEHOLDER, '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">'
                . '<rect width="200" height="200" fill="#ecfdf5"/>'
                . '<path d="M70 40 L100 55 L130 40 L170 70 L150 100 L135 90 L135 165 L65 165 L65 90 L50 100 L30 70 Z" '
                . 'fill="#34d399" stroke="#059669" stroke-width="4" stroke-linejoin="round"/></svg>');
        }
    }
}
