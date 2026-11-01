<?php

namespace Database\Seeders;

use App\Models\CategorieUpcycling;
use Illuminate\Database\Seeder;

class CategorieUpcyclingSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Sac', 'Sacs, tote bags et pochettes', 'shopping_bag'],
            ['Coussin', 'Housses de coussin et poufs', 'weekend'],
            ['Accessoire', 'Bandeaux, porte-clés, trousses', 'watch'],
            ['Décoration', 'Tapis, rideaux, objets déco', 'palette'],
            ['Vêtement', 'Nouveau vêtement à partir d\'un ancien', 'checkroom'],
            ['Autre', 'Autres idées de transformation', 'category'],
        ];

        foreach ($categories as [$nom, $description, $icone]) {
            // updateOrCreate : on peut relancer le seeder sans doublons
            CategorieUpcycling::updateOrCreate(
                ['nom' => $nom],
                ['description' => $description, 'icone' => $icone, 'actif' => true]
            );
        }
    }
}
