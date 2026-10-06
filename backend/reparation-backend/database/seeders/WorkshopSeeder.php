<?php

namespace Database\Seeders;

use App\Models\Workshop;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    public function run(): void
    {
        $workshops = [
            [
                'name' => 'Atelier Couture Bab Souika',
                'address' => '12 Rue de la Kasbah',
                'city' => 'Tunis',
                'latitude' => 36.7995,
                'longitude' => 10.1719,
                'phone' => '+216 71 000 001',
                'rating' => 4.6,
                'specialties' => ['Couture', 'Trou', 'Déchirure', 'Général'],
            ],
            [
                'name' => 'Pressing & Retouches Menzah',
                'address' => 'Avenue Habib Bourguiba',
                'city' => 'Ariana',
                'latitude' => 36.8665,
                'longitude' => 10.1647,
                'phone' => '+216 71 000 002',
                'rating' => 4.2,
                'specialties' => ['Tache', 'Nettoyage spécialisé', 'Général'],
            ],
            [
                'name' => 'Cordonnerie & Maroquinerie Sfax Centre',
                'address' => 'Rue Habib Maazoun',
                'city' => 'Sfax',
                'latitude' => 34.7398,
                'longitude' => 10.7600,
                'phone' => '+216 74 000 003',
                'rating' => 4.8,
                'specialties' => ['Fermeture cassée', 'Cuir', 'Général'],
            ],
            [
                'name' => 'Retouche Express Sousse',
                'address' => 'Rue Ibn Khaldoun',
                'city' => 'Sousse',
                'latitude' => 35.8256,
                'longitude' => 10.6411,
                'phone' => '+216 73 000 004',
                'rating' => 4.0,
                'specialties' => ['Bouton manquant', 'Usure', 'Général'],
            ],
        ];

        foreach ($workshops as $workshop) {
            Workshop::updateOrCreate(['name' => $workshop['name']], $workshop);
        }
    }
}
