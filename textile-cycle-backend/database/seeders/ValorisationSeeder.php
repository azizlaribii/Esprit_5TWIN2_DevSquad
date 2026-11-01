<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\Valorisation;
use Illuminate\Database\Seeder;

class ValorisationSeeder extends Seeder
{
    public function run(): void
    {
        $depots = Depot::all();

        if ($depots->isEmpty()) {
            $this->command->warn('Aucun dépôt : lance DepotSeeder avant.');
            return;
        }

        // Une valorisation par dépôt, rattachée à un dépôt existant (relation 1-N)
        foreach ($depots as $depot) {
            Valorisation::factory()->create(['depot_id' => $depot->id]);
        }
    }
}