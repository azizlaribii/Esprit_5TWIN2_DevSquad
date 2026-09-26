<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TexTileCycleSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Users
        DB::table('users')->insertOrIgnore([
            ['id' => 1, 'name' => 'Marie L.', 'email' => 'marie@example.com', 'password' => bcrypt('password'), 'role' => 'user', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Ahmed K.', 'email' => 'ahmed@example.com', 'password' => bcrypt('password'), 'role' => 'user', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Sophie M.', 'email' => 'sophie@example.com', 'password' => bcrypt('password'), 'role' => 'user', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Ateliers
        DB::table('ateliers')->insertOrIgnore([
            ['id' => 1, 'nom' => 'Atelier Recousu Paris', 'adresse' => '12 Rue Oberkampf, Paris', 'specialite' => 'Couture & Retouches', 'note' => 4.9, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nom' => 'EcoCouture Lyon', 'adresse' => '45 Rue de la République, Lyon', 'specialite' => 'Upcycling & Cuir', 'note' => 4.8, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Associations
        DB::table('associations')->insertOrIgnore([
            ['id' => 1, 'nom' => 'Emmaüs Solidarité Textile', 'adresse' => '8 Avenue de la Paix, Paris', 'beneficiaires_aides' => 320, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nom' => 'Le Relais Textile', 'adresse' => '15 Rue de l\'Avenir, Lille', 'beneficiaires_aides' => 210, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Depots
        DB::table('depots')->insertOrIgnore([
            ['user_id' => 1, 'categorie' => 'Vestes & Manteaux', 'quantite' => 3, 'etat' => 'Bon état', 'statut' => 'valide', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'categorie' => 'T-Shirts & Tops', 'quantite' => 8, 'etat' => 'Très bon état', 'statut' => 'en_attente', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'categorie' => 'Pantalons & Jeans', 'quantite' => 5, 'etat' => 'A réparer', 'statut' => 'valide', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Reparations
        DB::table('reparations')->insertOrIgnore([
            ['user_id' => 1, 'atelier_id' => 1, 'description' => 'Fermeture éclair manteau cuir', 'statut' => 'en_cours', 'cout' => 18.50, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 2, 'atelier_id' => 2, 'description' => 'Raccourcissement jean brut', 'statut' => 'terminee', 'cout' => 12.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Dons
        DB::table('dons')->insertOrIgnore([
            ['user_id' => 1, 'association_id' => 1, 'quantite_kg' => 14.5, 'statut' => 'distribue', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'association_id' => 2, 'quantite_kg' => 8.2, 'statut' => 'en_recuperation', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
