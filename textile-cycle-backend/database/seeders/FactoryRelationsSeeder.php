<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workshop;
use App\Models\RepairRequest;

/**
 * ReparationSeeder – Bonnes pratiques Laravel
 *
 * Démontre l'utilisation des factories avec les relations Eloquent
 * dans le module Réparation :
 *
 *   Relation 1-N : Workshop hasMany RepairRequest
 *   Relation N-1 : RepairRequest belongsTo User
 *   Relation N-1 : RepairRequest belongsTo Workshop
 */
class FactoryRelationsSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────────────────────
        // 1. Créer des utilisateurs clients (rôle user)
        //    → ils soumettront des demandes de réparation
        // ──────────────────────────────────────────────────────────────
        $clients = User::factory()
            ->roleUser()
            ->count(8)
            ->create();

        $this->command->info("👤 8 clients créés (rôle user)");

        // ──────────────────────────────────────────────────────────────
        // 2. Créer des ateliers avec leurs demandes de réparation (1-N)
        //    → Workshop hasMany RepairRequest
        //    → on utilise has() pour chaîner la relation
        // ──────────────────────────────────────────────────────────────
        $workshops = Workshop::factory()
            ->count(4)
            ->has(
                // Chaque atelier a 3 demandes en attente
                RepairRequest::factory()
                    ->enAttente()
                    ->count(3)
                    ->for($clients->random(), 'user'),   // relation belongsTo User
                'repairRequests'                          // relation hasMany côté Workshop
            )
            ->create();

        $this->command->info("🏪 4 ateliers créés avec 3 demandes en attente chacun (relation 1-N)");

        // ──────────────────────────────────────────────────────────────
        // 3. Ajouter des demandes en cours dans un atelier top-noté
        //    → factory state : topRated + enCours + severite elevee
        // ──────────────────────────────────────────────────────────────
        $atelierPremium = Workshop::factory()
            ->topRated()
            ->has(
                RepairRequest::factory()
                    ->enCours()
                    ->severite('elevee')
                    ->count(5)
                    ->for($clients->random(), 'user'),
                'repairRequests'
            )
            ->create();

        $this->command->info("⭐ 1 atelier premium créé avec 5 demandes en cours (sévérité élevée)");

        // ──────────────────────────────────────────────────────────────
        // 4. Demandes terminées réparties sur les ateliers existants
        //    → on utilise for() pour associer un atelier existant
        // ──────────────────────────────────────────────────────────────
        RepairRequest::factory()
            ->terminee()
            ->count(10)
            ->for($clients->random(), 'user')
            ->for($workshops->random(), 'workshop')   // relation belongsTo Workshop existant
            ->create();

        $this->command->info("✅ 10 demandes terminées créées sur les ateliers existants");

        // ──────────────────────────────────────────────────────────────
        // 5. Résumé
        // ──────────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->table(
            ['Entité', 'Total en base', 'Relation utilisée'],
            [
                ['Workshop',       Workshop::count(),      'hasMany RepairRequest (1-N)'],
                ['RepairRequest',  RepairRequest::count(),  'belongsTo Workshop + User'],
                ['User (clients)', $clients->count(),       'hasMany RepairRequest'],
            ]
        );
    }
}
