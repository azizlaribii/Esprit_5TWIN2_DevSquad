<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory pour l'entité User
 * Génère des utilisateurs avec différents rôles (user, atelier, association)
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'             => $this->faker->name(),
            'email'            => $this->faker->unique()->safeEmail(),
            'password'         => Hash::make('password'),
            'role'             => 'user',
            'remember_token'   => Str::random(10),
        ];
    }

    /** État : utilisateur simple (vendeur/acheteur) */
    public function roleUser(): static
    {
        return $this->state(fn () => ['role' => 'user']);
    }

    /** État : gestionnaire d'atelier */
    public function roleAtelier(): static
    {
        return $this->state(fn () => ['role' => 'atelier']);
    }

    /** État : responsable d'association */
    public function roleAssociation(): static
    {
        return $this->state(fn () => ['role' => 'association']);
    }
}
