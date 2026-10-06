<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    $this->call([
        TexTileCycleSeeder::class,
        MarketplaceSeeder::class,
        FactoryRelationsSeeder::class,
        DepotSeeder::class,
        TransformationSeeder::class,
    ]);
}
}