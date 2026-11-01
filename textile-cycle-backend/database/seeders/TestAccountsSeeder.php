<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Comptes de test (un par rôle) pour tester le projet en local.
 * À lancer avec : php artisan db:seed --class=TestAccountsSeeder
 * Mot de passe commun : Test1234!   (ne jamais utiliser en production)
 */
class TestAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $comptes = [
            ['Test Particulier', 'particulier@textilecycle.com', 'user'],
            ['Test Atelier',     'atelier@textilecycle.com',     'atelier'],
            ['Test Association', 'association@textilecycle.com', 'association'],
        ];

        foreach ($comptes as [$nom, $email, $role]) {
            User::unguarded(function () use ($nom, $email, $role) {
                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'              => $nom,
                        'password'          => Hash::make('Test1234!'),
                        'role'              => $role,
                        'email_verified_at' => now(),
                    ]
                );
            });
        }
    }
}