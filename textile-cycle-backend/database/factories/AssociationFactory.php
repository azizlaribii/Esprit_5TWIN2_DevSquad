<?php

namespace Database\Factories;

use App\Models\Association;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Association de démonstration (colonnes d'origine du projet + colonnes du module Don intelligent).
 * Le compte utilisateur lié est créé à la volée avec le rôle « association ».
 *
 * @extends Factory<Association>
 */
class AssociationFactory extends Factory
{
    protected $model = Association::class;

    /** Villes avec coordonnées approximatives : [latitude, longitude]. */
    private const CITIES = [
        'Tunis'     => [36.8065, 10.1815],
        'Ariana'    => [36.8665, 10.1647],
        'La Marsa'  => [36.8782, 10.3247],
        'Ben Arous' => [36.7531, 10.2189],
        'Sousse'    => [35.8256, 10.6084],
    ];

    public function definition(): array
    {
        [$city, $lat, $lng] = self::randomCity();

        return [
            'user_id'             => fn () => self::createUser('association'),
            'nom'                 => 'Association ' . fake()->unique()->lastName() . ' Solidarité',
            'adresse'             => fake()->streetAddress(),
            'beneficiaires_aides' => fake()->numberBetween(50, 400),
            'description'         => fake()->sentence(12),
            'city'                => $city,
            'lat'                 => $lat,
            'lng'                 => $lng,
            'accepted_conditions' => ['neuf', 'bon', 'usage'],
            'accepted_categories' => [], // vide = toutes les catégories
            'capacity'            => 200,
            'opening_hours'       => 'Lun-ven 9h-17h',
            'phone'               => '+216 70 000 000',
            'verified_at'         => now(),
        ];
    }

    /** Association inscrite mais pas encore vérifiée par un admin : jamais proposée aux donateurs. */
    public function unverified(): static
    {
        return $this->state(['verified_at' => null]);
    }

    public function inCity(string $city): static
    {
        [$lat, $lng] = self::CITIES[$city];

        return $this->state(['city' => $city, 'lat' => $lat, 'lng' => $lng]);
    }

    /** @return array{0: string, 1: float, 2: float} */
    public static function randomCity(): array
    {
        $city = fake()->randomElement(array_keys(self::CITIES));

        return [$city, ...self::CITIES[$city]];
    }

    /**
     * Crée un compte de démonstration sans passer par UserFactory (la table users du projet
     * n'a pas de colonne email_verified_at) et renvoie son identifiant.
     */
    public static function createUser(string $role): int
    {
        static $hash = null;
        $hash ??= Hash::make('password');

        return User::forceCreate([
            'name'     => fake()->name(),
            'email'    => fake()->unique()->safeEmail(),
            'password' => $hash,
            'role'     => $role,
        ])->id;
    }
}
