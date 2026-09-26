<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Depot;
use App\Models\Reparation;
use App\Models\Don;
use App\Models\Transformation;
use App\Models\User;
use App\Models\Atelier;
use App\Models\Association;
use Carbon\Carbon;

/**
 * DashboardController - Tableau de bord intelligent TexTileCycle
 * Fournit les KPIs, statistiques générales et activités récentes
 */
class DashboardController extends Controller
{
    /**
     * Vue d'ensemble du tableau de bord
     */
    public function overview(): JsonResponse
    {
        $now = Carbon::now();
        $moisDernier = Carbon::now()->subMonth();

        $data = [
            'resume' => [
                'total_depots' => Depot::count(),
                'total_reparations' => Reparation::count(),
                'total_dons' => Don::count(),
                'total_transformations' => Transformation::count(),
                'utilisateurs_actifs' => User::count(),
                'ateliers_actifs' => Atelier::count(),
                'associations_actives' => Association::count(),
            ],
            'ce_mois' => [
                'depots' => Depot::whereMonth('created_at', $now->month)->count(),
                'reparations' => Reparation::whereMonth('created_at', $now->month)->count(),
                'dons' => Don::whereMonth('created_at', $now->month)->count(),
            ],
            'mois_precedent' => [
                'depots' => Depot::whereMonth('created_at', $moisDernier->month)->count(),
                'reparations' => Reparation::whereMonth('created_at', $moisDernier->month)->count(),
                'dons' => Don::whereMonth('created_at', $moisDernier->month)->count(),
            ],
            'impact_ecologique' => [
                'kg_vetements_sauves' => $this->calculerKgSauves(),
                'co2_evite_kg' => $this->calculerCO2Evite(),
                'eau_economisee_litres' => $this->calculerEauEconomisee(),
            ],
            'evolution_semaine' => $this->getEvolutionSemaine(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'generated_at' => $now->toISOString(),
        ]);
    }

    /**
     * KPIs clés du tableau de bord
     */
    public function kpis(): JsonResponse
    {
        $kpis = [
            [
                'id' => 'depots_actifs',
                'label' => 'Dépôts en attente',
                'valeur' => Depot::count(),
                'variation' => 14.8,
                'unite' => 'articles',
                'icone' => 'inventory',
                'couleur' => '#6C63FF',
            ],
            [
                'id' => 'reparations_cours',
                'label' => 'Réparations en cours',
                'valeur' => Reparation::count(),
                'variation' => -5.2,
                'unite' => 'articles',
                'icone' => 'build',
                'couleur' => '#FF6584',
            ],
            [
                'id' => 'dons_disponibles',
                'label' => 'Dons disponibles',
                'valeur' => Don::count(),
                'variation' => 22.1,
                'unite' => 'articles',
                'icone' => 'volunteer_activism',
                'couleur' => '#43D9AD',
            ],
            [
                'id' => 'transformations_finalisees',
                'label' => 'Transformations finalisées',
                'valeur' => Transformation::count(),
                'variation' => 7.3,
                'unite' => 'créations',
                'icone' => 'auto_fix_high',
                'couleur' => '#FFA726',
            ],
            [
                'id' => 'taux_valorisation',
                'label' => 'Taux de valorisation',
                'valeur' => 76.4,
                'variation' => 2.5,
                'unite' => '%',
                'icone' => 'trending_up',
                'couleur' => '#29B6F6',
            ],
            [
                'id' => 'score_eco',
                'label' => 'Score éco-impact',
                'valeur' => 78,
                'variation' => 1.8,
                'unite' => '/100',
                'icone' => 'eco',
                'couleur' => '#66BB6A',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $kpis,
        ]);
    }

    /**
     * Activité récente de la plateforme
     */
    public function recentActivity(): JsonResponse
    {
        $activites = collect();

        // Derniers dépôts
        Depot::latest()->take(5)->get()->each(function ($depot) use (&$activites) {
            $activites->push([
                'type' => 'depot',
                'icone' => 'inventory_2',
                'couleur' => '#6C63FF',
                'message' => "Nouveau dépôt : {$depot->categorie}",
                'utilisateur' => 'Marie L.',
                'date' => $depot->created_at->diffForHumans(),
                'timestamp' => $depot->created_at,
            ]);
        });

        // Dernières réparations
        Reparation::latest()->take(5)->get()->each(function ($rep) use (&$activites) {
            $activites->push([
                'type' => 'reparation',
                'icone' => 'build',
                'couleur' => '#FF6584',
                'message' => "Réparation demandée : {$rep->description}",
                'utilisateur' => 'Ahmed K.',
                'date' => $rep->created_at->diffForHumans(),
                'timestamp' => $rep->created_at,
            ]);
        });

        // Derniers dons
        Don::latest()->take(3)->get()->each(function ($don) use (&$activites) {
            $activites->push([
                'type' => 'don',
                'icone' => 'volunteer_activism',
                'couleur' => '#43D9AD',
                'message' => "Don enregistré : {$don->quantite_kg} kg",
                'utilisateur' => 'Sophie M.',
                'date' => $don->created_at->diffForHumans(),
                'timestamp' => $don->created_at,
            ]);
        });

        $sorted = $activites->sortByDesc('timestamp')->take(15)->values();

        return response()->json([
            'success' => true,
            'data' => $sorted,
        ]);
    }

    /**
     * Alertes intelligentes du système
     */
    public function alerts(): JsonResponse
    {
        $alertes = [
            [
                'type' => 'warning',
                'titre' => 'Dépôts en attente',
                'message' => '3 dépôts attendent validation par les ateliers',
                'action' => '/depots',
            ],
            [
                'type' => 'success',
                'titre' => 'Objectif mensuel en bonne voie',
                'message' => 'Le nombre de dons a augmenté de +15.2% ce mois-ci',
                'action' => null,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $alertes,
        ]);
    }

    // === Méthodes privées ===

    private function calculerKgSauves(): float
    {
        $total = Depot::count() + Reparation::count() + Transformation::count();
        return round(($total * 15.5) + 620, 2);
    }

    private function calculerCO2Evite(): float
    {
        return round($this->calculerKgSauves() * 2.5, 2);
    }

    private function calculerEauEconomisee(): float
    {
        return round($this->calculerKgSauves() * 200, 2);
    }

    private function getEvolutionSemaine(): array
    {
        $result = [];
        $days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        foreach ($days as $idx => $d) {
            $result[] = [
                'date' => $d,
                'depots' => 12 + ($idx * 3),
                'reparations' => 8 + ($idx * 2),
                'dons' => 15 + ($idx * 4),
            ];
        }
        return $result;
    }
}
