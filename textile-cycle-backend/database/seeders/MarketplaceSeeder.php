<?php

namespace Database\Seeders;

use App\Models\ArticleMarketplace;
use App\Models\DemandeMarketplace;
use App\Models\EvaluationVendeur;
use App\Models\FavoriMarketplace;
use App\Models\User;
use Database\Factories\AssociationFactory;
use Illuminate\Database\Seeder;

/**
 * MarketplaceSeeder – Peuple la base avec des données réalistes pour le module Marketplace.
 *
 * Relations couvertes :
 *  ┌──────────────────────────────────────────────────────────────────┐
 *  │  User  ─(1-N)──►  ArticleMarketplace                            │
 *  │                         │                                        │
 *  │                   ──────┼──────────────────────────────          │
 *  │                   ↓              ↓               ↓               │
 *  │             FavoriMarketplace  DemandeMarketplace  EvaluationV.  │
 *  │               (N-N via pivot)   (acheteur → art)  (evaluateur+  │
 *  │               user ↔ article                        vendeur)     │
 *  └──────────────────────────────────────────────────────────────────┘
 *
 * Usage :
 *   php artisan db:seed --class=MarketplaceSeeder
 */
class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------------
        // 1. Création des utilisateurs (vendeurs + acheteurs)
        // ------------------------------------------------------------------
        // 3 vendeurs réguliers + 5 acheteurs (peuvent aussi vendre)
        $vendeurs  = $this->creerUtilisateurs(3, 'vendeur');
        $acheteurs = $this->creerUtilisateurs(5, 'acheteur');

        // Pool combiné pour les favoris & demandes
        $tousLesUsers = $vendeurs->merge($acheteurs);

        // ------------------------------------------------------------------
        // 2. Création des articles – répartis par type
        // ------------------------------------------------------------------

        // Chaque vendeur a plusieurs articles en vente
        $articlesVente = collect();
        foreach ($vendeurs as $vendeur) {
            $articles = ArticleMarketplace::factory()
                ->count(4)
                ->vente()
                ->disponible()
                ->pourVendeur($vendeur)
                ->create();
            $articlesVente = $articlesVente->merge($articles);
        }

        // Quelques articles en échange
        $articlesEchange = ArticleMarketplace::factory()
            ->count(3)
            ->echange()
            ->disponible()
            ->pourVendeur($vendeurs->random())
            ->create();

        // Quelques dons
        $articlesDon = ArticleMarketplace::factory()
            ->count(2)
            ->don()
            ->disponible()
            ->pourVendeur($vendeurs->random())
            ->create();

        // Articles déjà vendus (utiles pour les évaluations)
        $articlesVendus = ArticleMarketplace::factory()
            ->count(4)
            ->vente()
            ->vendu()
            ->create(function (array $attributes) use ($vendeurs) {
                return ['user_id' => $vendeurs->random()->id];
            });

        $tousLesArticles = $articlesVente
            ->merge($articlesEchange)
            ->merge($articlesDon);

        // ------------------------------------------------------------------
        // 3. Favoris – relation User ↔ ArticleMarketplace (contrainte unique)
        // ------------------------------------------------------------------
        $this->creerFavoris($tousLesUsers, $tousLesArticles);

        // ------------------------------------------------------------------
        // 4. Demandes d'achat – relation Acheteur → Article (en_attente)
        // ------------------------------------------------------------------
        $this->creerDemandes($acheteurs, $articlesVente);

        // ------------------------------------------------------------------
        // 5. Évaluations – sur les articles vendus
        //    Règle : evaluateur ≠ vendeur
        // ------------------------------------------------------------------
        $this->creerEvaluations($articlesVendus, $acheteurs);

        $this->command->info('✅  MarketplaceSeeder terminé avec succès.');
        $this->command->table(
            ['Entité', 'Enregistrements créés'],
            [
                ['Articles (vente)',   $articlesVente->count()],
                ['Articles (échange)', $articlesEchange->count()],
                ['Articles (don)',     $articlesDon->count()],
                ['Articles (vendus)',  $articlesVendus->count()],
                ['Favoris',           FavoriMarketplace::count()],
                ['Demandes',          DemandeMarketplace::count()],
                ['Évaluations',       EvaluationVendeur::count()],
            ]
        );
    }

    // -----------------------------------------------------------------------
    //  Méthodes privées – organisation du seeder
    // -----------------------------------------------------------------------

    /**
     * Crée des utilisateurs via la méthode helper partagée de AssociationFactory.
     * Retourne une Collection d'objets User hydratés.
     */
    private function creerUtilisateurs(int $count, string $role): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(function () use ($role) {
            $userId = AssociationFactory::createUser($role);
            return User::find($userId);
        });
    }

    /**
     * Crée des favoris aléatoires en respectant la contrainte UNIQUE (user_id, article_id).
     * Chaque utilisateur met entre 1 et 3 articles en favori.
     */
    private function creerFavoris(
        \Illuminate\Support\Collection $users,
        \Illuminate\Support\Collection $articles
    ): void {
        $dejaCrees = []; // évite les doublons

        foreach ($users as $user) {
            // Sélectionne 1 à 3 articles aléatoires (différents du vendeur de l'article)
            $candidats = $articles->filter(fn ($a) => $a->user_id !== $user->id);
            if ($candidats->isEmpty()) continue;

            $nbFavoris = min(fake()->numberBetween(1, 3), $candidats->count());

            $candidats->random($nbFavoris)->each(function ($article) use ($user, &$dejaCrees) {
                $key = "{$user->id}_{$article->id}";
                if (isset($dejaCrees[$key])) return;

                FavoriMarketplace::firstOrCreate([
                    'user_id'    => $user->id,
                    'article_id' => $article->id,
                ]);

                $dejaCrees[$key] = true;
            });
        }
    }

    /**
     * Crée des demandes d'achat variées.
     * Chaque acheteur envoie 1 à 2 demandes sur des articles différents.
     * Répartition des statuts : 60 % en_attente, 20 % acceptée, 20 % refusée.
     */
    private function creerDemandes(
        \Illuminate\Support\Collection $acheteurs,
        \Illuminate\Support\Collection $articles
    ): void {
        $statuts = ['en_attente', 'en_attente', 'en_attente', 'acceptee', 'refusee'];

        foreach ($acheteurs as $acheteur) {
            $candidats = $articles->filter(fn ($a) => $a->user_id !== $acheteur->id);
            if ($candidats->isEmpty()) continue;

            $nbDemandes = min(fake()->numberBetween(1, 2), $candidats->count());

            $candidats->random($nbDemandes)->each(function ($article) use ($acheteur, $statuts) {
                DemandeMarketplace::factory()
                    ->pourArticle($article)
                    ->parAcheteur($acheteur)
                    ->create(['statut' => fake()->randomElement($statuts)]);
            });
        }
    }

    /**
     * Crée des évaluations sur les articles vendus.
     * Règle métier : l'évaluateur doit être différent du vendeur.
     */
    private function creerEvaluations(
        \Illuminate\Support\Collection $articlesVendus,
        \Illuminate\Support\Collection $acheteurs
    ): void {
        foreach ($articlesVendus as $article) {
            // Sélectionne un évaluateur différent du vendeur
            $evaluateurs = $acheteurs->filter(fn ($u) => $u->id !== $article->user_id);
            if ($evaluateurs->isEmpty()) continue;

            $evaluateur = $evaluateurs->random();

            // Note biaisée vers le positif (70 % excellente/bonne)
            $noteFactory = fake()->randomElement([
                'excellente', 'excellente', 'bonne', 'bonne', 'moyenne', 'mauvaise',
            ]);

            EvaluationVendeur::factory()
                ->pourArticle($article)
                ->parEvaluateur($evaluateur)
                ->{$noteFactory}()
                ->create();
        }
    }
}
