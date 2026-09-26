<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Depot;
use App\Models\Reparation;
use App\Models\Don;
use App\Models\Transformation;
use Carbon\Carbon;

/**
 * StatistiqueController - Statistiques complètes pour la plateforme TexTileCycle
 * Statistiques pour utilisateurs, ateliers et associations
 */
class StatistiqueController extends Controller
{
    /**
     * Statistiques des dépôts
     */
    public function depots(): JsonResponse
    {
        $data = [
            'total' => Depot::count(),
            'par_statut' => Depot::selectRaw('statut, COUNT(*) as count')->groupBy('statut')->get(),
            'par_categorie' => Depot::selectRaw('categorie, COUNT(*) as count')->groupBy('categorie')->orderByDesc('count')->get(),
            'par_etat' => Depot::selectRaw('etat, COUNT(*) as count')->groupBy('etat')->get(),
            'evolution_mensuelle' => $this->getEvolutionMensuelle(Depot::class),
            'taux_valorisation' => $this->calculerTaux(Depot::class, 'valorise'),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Statistiques des réparations
     */
    public function reparations(): JsonResponse
    {
        $data = [
            'total' => Reparation::count(),
            'par_statut' => Reparation::selectRaw('statut, COUNT(*) as count')->groupBy('statut')->get(),
            'par_type' => Reparation::selectRaw('type_reparation, COUNT(*) as count')->groupBy('type_reparation')->orderByDesc('count')->get(),
            'duree_moyenne_jours' => Reparation::where('statut', 'terminee')
                ->selectRaw('AVG(DATEDIFF(date_fin, date_debut)) as duree_moy')
                ->value('duree_moy'),
            'evolution_mensuelle' => $this->getEvolutionMensuelle(Reparation::class),
            'taux_completion' => $this->calculerTaux(Reparation::class, 'terminee'),
            'par_atelier' => Reparation::selectRaw('atelier_id, COUNT(*) as count')
                ->with('atelier:id,nom')
                ->groupBy('atelier_id')
                ->orderByDesc('count')
                ->take(10)
                ->get(),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Statistiques des dons
     */
    public function dons(): JsonResponse
    {
        $data = [
            'total' => Don::count(),
            'par_statut' => Don::selectRaw('statut, COUNT(*) as count')->groupBy('statut')->get(),
            'total_articles' => Don::sum('nb_articles'),
            'evolution_mensuelle' => $this->getEvolutionMensuelle(Don::class),
            'par_association' => Don::selectRaw('association_id, SUM(nb_articles) as total_articles')
                ->with('association:id,nom')
                ->groupBy('association_id')
                ->orderByDesc('total_articles')
                ->take(10)
                ->get(),
            'taux_attribution' => $this->calculerTaux(Don::class, 'attribue'),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Statistiques des transformations
     */
    public function transformations(): JsonResponse
    {
        $data = [
            'total' => Transformation::count(),
            'par_statut' => Transformation::selectRaw('statut, COUNT(*) as count')->groupBy('statut')->get(),
            'par_type' => Transformation::selectRaw('type_transformation, COUNT(*) as count')->groupBy('type_transformation')->orderByDesc('count')->get(),
            'evolution_mensuelle' => $this->getEvolutionMensuelle(Transformation::class),
            'taux_completion' => $this->calculerTaux(Transformation::class, 'terminee'),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Catégories populaires
     */
    public function categories(): JsonResponse
    {
        $categories = Depot::selectRaw('categorie, COUNT(*) as total, 
            SUM(CASE WHEN statut = "valorise" THEN 1 ELSE 0 END) as valorises')
            ->groupBy('categorie')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'categorie' => $row->categorie,
                'total' => $row->total,
                'valorises' => $row->valorises,
                'taux_valorisation' => $row->total > 0 ? round(($row->valorises / $row->total) * 100, 1) : 0,
            ]);

        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Évolution mensuelle globale
     */
    public function evolutionMensuelle(): JsonResponse
    {
        $result = [];
        for ($i = 11; $i >= 0; $i--) {
            $mois = Carbon::now()->subMonths($i);
            $result[] = [
                'mois' => $mois->format('M Y'),
                'mois_num' => $mois->format('Y-m'),
                'depots' => Depot::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count(),
                'reparations' => Reparation::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count(),
                'dons' => Don::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count(),
                'transformations' => Transformation::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count(),
            ];
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Répartition par type d'action
     */
    public function repartitionParType(): JsonResponse
    {
        $data = [
            ['type' => 'Dépôts', 'count' => Depot::count(), 'couleur' => '#6C63FF'],
            ['type' => 'Réparations', 'count' => Reparation::count(), 'couleur' => '#FF6584'],
            ['type' => 'Dons', 'count' => Don::count(), 'couleur' => '#43D9AD'],
            ['type' => 'Transformations', 'count' => Transformation::count(), 'couleur' => '#FFA726'],
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Top ateliers par performance
     */
    public function topAteliers(): JsonResponse
    {
        $ateliers = \App\Models\Atelier::withCount([
            'reparations',
            'reparations as reparations_terminees_count' => fn($q) => $q->where('statut', 'terminee'),
        ])
            ->orderByDesc('reparations_terminees_count')
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'nom' => $a->nom,
                'ville' => $a->ville,
                'total_reparations' => $a->reparations_count,
                'reparations_terminees' => $a->reparations_terminees_count,
                'taux_completion' => $a->reparations_count > 0
                    ? round(($a->reparations_terminees_count / $a->reparations_count) * 100, 1)
                    : 0,
                'note_moyenne' => $a->note_moyenne ?? 0,
            ]);

        return response()->json(['success' => true, 'data' => $ateliers]);
    }

    /**
     * Top associations par dons reçus
     */
    public function topAssociations(): JsonResponse
    {
        $associations = \App\Models\Association::withCount('dons')
            ->withSum('dons', 'nb_articles')
            ->orderByDesc('dons_count')
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'nom' => $a->nom,
                'ville' => $a->ville,
                'total_dons' => $a->dons_count,
                'total_articles' => $a->dons_sum_nb_articles ?? 0,
                'beneficiaires' => $a->nb_beneficiaires ?? 0,
            ]);

        return response()->json(['success' => true, 'data' => $associations]);
    }

    /**
     * Impact écologique global
     */
    public function impactEcologique(): JsonResponse
    {
        $articles_sauves = Depot::where('statut', 'valorise')->count()
            + Reparation::where('statut', 'terminee')->count()
            + Transformation::where('statut', 'terminee')->count();

        $dons_total_articles = Don::where('statut', 'attribue')->sum('nb_articles');

        $data = [
            'articles_sauves' => $articles_sauves,
            'kg_textiles_sauves' => $articles_sauves * 0.5,
            'co2_evite_kg' => $articles_sauves * 1.25, // 2.5 kg CO2 par kg textile
            'eau_economisee_litres' => $articles_sauves * 200,
            'equivalent_arbres' => round($articles_sauves * 1.25 / 21), // 21kg CO2 absorbé/arbre/an
            'articles_donnes' => $dons_total_articles,
            'familles_aidees' => intval($dons_total_articles / 5),
            'evolution_co2_mensuel' => $this->getEvolutionCO2Mensuel(),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Statistiques par région
     */
    public function parRegion(): JsonResponse
    {
        // Données simulées - en production, utiliser un champ géographique
        $regions = [
            ['region' => 'Île-de-France', 'depots' => 245, 'reparations' => 189, 'dons' => 312],
            ['region' => 'PACA', 'depots' => 178, 'reparations' => 134, 'dons' => 201],
            ['region' => 'Auvergne-Rhône-Alpes', 'depots' => 156, 'reparations' => 112, 'dons' => 189],
            ['region' => 'Nouvelle-Aquitaine', 'depots' => 134, 'reparations' => 98, 'dons' => 156],
            ['region' => 'Occitanie', 'depots' => 112, 'reparations' => 87, 'dons' => 134],
        ];

        return response()->json(['success' => true, 'data' => $regions]);
    }

    /**
     * Utilisateurs actifs
     */
    public function utilisateursActifs(): JsonResponse
    {
        $data = [
            'total' => \App\Models\Utilisateur::count(),
            'actifs_7j' => \App\Models\Utilisateur::where('derniere_connexion', '>=', Carbon::now()->subDays(7))->count(),
            'actifs_30j' => \App\Models\Utilisateur::where('derniere_connexion', '>=', Carbon::now()->subDays(30))->count(),
            'nouveaux_ce_mois' => \App\Models\Utilisateur::whereMonth('created_at', Carbon::now()->month)->count(),
            'par_role' => \App\Models\Utilisateur::selectRaw('role, COUNT(*) as count')->groupBy('role')->get(),
            'evolution_inscriptions' => $this->getEvolutionMensuelle(\App\Models\Utilisateur::class),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    // === Helpers privés ===

    private function getEvolutionMensuelle(string $model): array
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = Carbon::now()->subMonths($i);
            $result[] = [
                'mois' => $mois->format('M Y'),
                'count' => $model::whereYear('created_at', $mois->year)
                    ->whereMonth('created_at', $mois->month)
                    ->count(),
            ];
        }
        return $result;
    }

    private function calculerTaux(string $model, string $statut): float
    {
        $total = $model::count();
        if ($total === 0) return 0;
        $valeur = $model::where('statut', $statut)->count();
        return round(($valeur / $total) * 100, 1);
    }

    private function getEvolutionCO2Mensuel(): array
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = Carbon::now()->subMonths($i);
            $articles = Depot::where('statut', 'valorise')
                ->whereYear('created_at', $mois->year)
                ->whereMonth('created_at', $mois->month)
                ->count();
            $result[] = [
                'mois' => $mois->format('M Y'),
                'co2_kg' => round($articles * 1.25, 2),
            ];
        }
        return $result;
    }
}
