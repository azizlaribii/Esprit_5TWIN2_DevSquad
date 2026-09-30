<?php

namespace Database\Seeders;

use App\Models\Association;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Données de démonstration : 1 admin, 2 donateurs, 5 associations fictives (4 vérifiées, 1 non vérifiée).
 * Tous les comptes ont le mot de passe « password ». Les associations sont FICTIVES.
 *
 *   php artisan db:seed --class=DonIntelligentSeeder
 */
class DonIntelligentSeeder extends Seeder
{
    public function run(): void
    {
        $this->user('admin@example.test', 'Admin TexTileCycle', 'admin');
        $this->user('donateur1@example.test', 'Amel Donatrice', 'user', '+216 20 000 001');
        $this->user('donateur2@example.test', 'Karim Donateur', 'user', '+216 20 000 002');

        // Coordonnées approximatives des centres-villes.
        $associations = [
            [
                'email' => 'assoc-tunis@example.test',
                'name'  => 'Démo · Entraide Tunis',
                'city'  => 'Tunis', 'lat' => 36.8065, 'lng' => 10.1815,
                'capacity' => 300, 'verified' => true,
                'conditions' => ['neuf', 'bon', 'usage'], 'categories' => [],
                'needs' => [
                    ['manteau', 'adulte', null, 'mixte', 'hiver', 40, 5],
                    ['pull', 'adulte', null, 'mixte', 'hiver', 30, 4],
                    ['pantalon', 'adulte', null, 'mixte', null, 25, 3],
                ],
            ],
            [
                'email' => 'assoc-marsa@example.test',
                'name'  => 'Démo · Vêtements Solidaires La Marsa',
                'city'  => 'La Marsa', 'lat' => 36.8782, 'lng' => 10.3247,
                'capacity' => 150, 'verified' => true,
                'conditions' => ['neuf', 'bon'],
                'categories' => ['manteau', 'pull', 'haut', 'pantalon', 'robe_jupe'],
                'needs' => [
                    ['manteau', 'enfant', null, 'mixte', 'hiver', 20, 4],
                    ['haut', 'adulte', 'M', 'femme', 'ete', 15, 2],
                ],
            ],
            [
                'email' => 'assoc-ariana@example.test',
                'name'  => 'Démo · Enfance Ariana',
                'city'  => 'Ariana', 'lat' => 36.8665, 'lng' => 10.1647,
                'capacity' => 200, 'verified' => true,
                'conditions' => ['neuf', 'bon', 'usage'], 'categories' => [],
                'needs' => [
                    ['manteau', 'enfant', '5-6a', 'mixte', 'hiver', 30, 5],
                    ['pull', 'enfant', null, 'mixte', 'hiver', 30, 4],
                    ['chaussures', 'enfant', null, 'mixte', null, 15, 3],
                ],
            ],
            [
                'email' => 'assoc-sfax@example.test',
                'name'  => 'Démo · Sfax Solidarité',
                'city'  => 'Sfax', 'lat' => 34.7406, 'lng' => 10.7603,
                'capacity' => 200, 'verified' => true,
                'conditions' => ['neuf', 'bon', 'usage'], 'categories' => [],
                'needs' => [
                    ['manteau', 'adulte', null, 'mixte', 'hiver', 50, 5],
                ],
            ],
            [
                'email' => 'assoc-attente@example.test',
                'name'  => 'Démo · Association en attente de vérification',
                'city'  => 'Ben Arous', 'lat' => 36.7531, 'lng' => 10.2189,
                'capacity' => 100, 'verified' => false,
                'conditions' => [], 'categories' => [],
                'needs' => [
                    ['manteau', 'enfant', null, 'mixte', 'hiver', 100, 5],
                ],
            ],
        ];

        foreach ($associations as $data) {
            $user = $this->user($data['email'], $data['name'], 'association');

            $association = Association::updateOrCreate(['user_id' => $user->id], [
                'name'                => $data['name'],
                'description'         => 'Association fictive créée pour la démonstration.',
                'city'                => $data['city'],
                'lat'                 => $data['lat'],
                'lng'                 => $data['lng'],
                'capacity'            => $data['capacity'],
                'accepted_conditions' => $data['conditions'],
                'accepted_categories' => $data['categories'],
                'opening_hours'       => 'Lun-ven 9h-17h',
                'phone'               => '+216 70 000 000',
                'verified_at'         => $data['verified'] ? now() : null,
            ]);

            $association->needs()->delete();

            foreach ($data['needs'] as [$category, $age, $size, $gender, $season, $qty, $urgency]) {
                $association->needs()->create([
                    'category'        => $category,
                    'age_group'       => $age,
                    'size'            => $size,
                    'gender'          => $gender,
                    'season'          => $season,
                    'quantity_needed' => $qty,
                    'urgency'         => $urgency,
                    'expires_at'      => now()->addMonths(2)->toDateString(),
                ]);
            }
        }
    }

    private function user(string $email, string $name, string $role, ?string $phone = null): User
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name'              => $name,
            'password'          => Hash::make('password'),
            'role'              => $role,
            'phone'             => $phone,
            
        ])->save();

        return $user;
    }
}
