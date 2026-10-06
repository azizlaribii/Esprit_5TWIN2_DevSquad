<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\Transformation;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransformationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(3)->get();
        if ($users->isEmpty()) {
            $users = User::factory(3)->create();
        }

        $garments = [
            ['Jeans & Pantalons', 'Bon état'],
            ['T-Shirts & Tops', 'État correct'],
            ['Vestes & Manteaux', 'Très bon état'],
        ];

        foreach ($users as $user) {
            foreach ($garments as [$categorie, $etat]) {
                $depot = Depot::create([
                    'user_id'   => $user->id,
                    'categorie' => $categorie,
                    'quantite'  => 1,
                    'etat'      => $etat,
                ]);

                // Relation : chaque dépôt a 1 à 2 projets d'upcycling
                Transformation::factory(rand(1, 2))->create([
                    'user_id'  => $user->id,
                    'depot_id' => $depot->id,
                ]);
            }
        }
    }
}
