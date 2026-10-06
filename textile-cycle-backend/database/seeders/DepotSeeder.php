<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepotSeeder extends Seeder
{
    public function run(): void
    {
        // Réutilise les utilisateurs existants (créés par TexTileCycleSeeder)
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory(3)->create();
        }

        // 15 dépôts répartis sur les utilisateurs existants (relation 1-N)
        Depot::factory(15)->recycle($users)->create();
    }
}