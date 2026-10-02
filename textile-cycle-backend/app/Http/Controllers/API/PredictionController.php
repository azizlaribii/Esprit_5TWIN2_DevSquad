<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Models\Depot;
use App\Models\Reparation;
use App\Models\Don;
use Carbon\Carbon;

/**
 * PredictionController - Interface avec le service IA Python
 * Génère des prédictions, tendances et recommandations intelligentes
 */
class PredictionController extends Controller
{
    private string $aiServiceUrl;

    public function __construct()
    {
        $this->aiServiceUrl = config('services.ai.url', env('AI_SERVICE_URL', 'http://localhost:8001'));
    }

    /**
     * Prédictions des dépôts futurs
     */
    public function predictDepots(): JsonResponse
    {
        $historique = $this->getHistoriqueDepots();

        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/predict/depots", [
                    'historique' => $historique,
                    'horizon' => 30, // jours
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'source' => 'ai_service',
                ]);
            }
        } catch (\Exception $e) {
            // Fallback: prédiction simple
        }

        // Fallback: tendance linéaire simple
        $predictions = $this->predictionSimple('depot');

        return response()->json([
            'success' => true,
            'data' => $predictions,
            'source' => 'fallback',
        ]);
    }

    /**
     * Prédictions des réparations
     */
    public function predictReparations(): JsonResponse
    {
        $historique = $this->getHistoriqueReparations();

        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/predict/reparations", [
                    'historique' => $historique,
                    'horizon' => 30,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'source' => 'ai_service',
                ]);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return response()->json([
            'success' => true,
            'data' => $this->predictionSimple('reparation'),
            'source' => 'fallback',
        ]);
    }

    /**
     * Prédictions des dons
     */
    public function predictDons(): JsonResponse
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/predict/dons", [
                    'historique' => $this->getHistoriqueDons(),
                    'horizon' => 30,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'source' => 'ai_service',
                ]);
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'data' => $this->predictionSimple('don'),
            'source' => 'fallback',
        ]);
    }

    /**
     * Tendances générales (analyse IA)
     */
    public function tendances(): JsonResponse
    {
        $donnees = [
            'depots_par_categorie' => $this->getDepotsParCategorie(),
            'evolution_mensuelle' => $this->getEvolutionMensuelle(),
            'taux_valorisation_mensuel' => $this->getTauxValorisationMensuel(),
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/analyse/tendances", $donnees);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'source' => 'ai_service',
                ]);
            }
        } catch (\Exception $e) {}

        // Tendances calculées localement
        $tendances = $this->calculerTendancesLocales($donnees);

        return response()->json([
            'success' => true,
            'data' => $tendances,
            'source' => 'local',
        ]);
    }

    /**
     * Recommandations personnalisées IA
     */
    public function recommandations(): JsonResponse
    {
        $contexte = [
            'depots_en_attente' => Depot::where('statut', 'en_attente')->count(),
            'categories_dominantes' => $this->getDepotsParCategorie(),
            'ateliers_disponibles' => \App\Models\Atelier::where('statut', 'actif')->count(),
            'associations_besoins' => $this->getBesoinsAssociations(),
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/recommandations", $contexte);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {}

        // Recommandations basées sur des règles
        $recommandations = $this->genererRecommandationsRegles($contexte);

        return response()->json([
            'success' => true,
            'data' => $recommandations,
        ]);
    }

    /**
     * Détection d'anomalies
     */
    public function detecterAnomalies(): JsonResponse
    {
        $donnees = [
            'serie_depots' => $this->getSerieTemporelle('depot'),
            'serie_reparations' => $this->getSerieTemporelle('reparation'),
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/anomalies", $donnees);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'data' => ['anomalies' => [], 'message' => 'Service IA temporairement indisponible'],
        ]);
    }

    /**
     * Catégories populaires avec prédictions
     */
    public function categoriesPopulaires(): JsonResponse
    {
        $categories = $this->getDepotsParCategorie();

        try {
            $response = Http::timeout(30)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/categories/tendances", [
                    'categories' => $categories,
                    'horizon' => 30,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {}

        $topCategories = collect($categories)
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->map(fn($cat) => array_merge($cat, [
                'tendance' => 'stable',
                'prediction_mois_prochain' => intval($cat['count'] * 1.05),
            ]));

        return response()->json([
            'success' => true,
            'data' => ['categories' => $topCategories],
        ]);
    }

    /**
     * Rapport complet d'analyse IA
     */
    public function rapportComplet(): JsonResponse
    {
        $donnees = [
            'periode' => [
                'debut' => Carbon::now()->subMonths(6)->toDateString(),
                'fin' => Carbon::now()->toDateString(),
            ],
            'totaux' => [
                'depots' => Depot::count(),
                'reparations' => Reparation::count(),
                'dons' => Don::count(),
            ],
            'evolution' => $this->getEvolutionMensuelle(),
            'categories' => $this->getDepotsParCategorie(),
        ];

        try {
            $response = Http::timeout(60)
                ->withHeaders(['X-AI-Secret' => env('AI_SERVICE_SECRET')])
                ->post("{$this->aiServiceUrl}/rapport/complet", $donnees);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'data' => [
                'resume_executif' => 'Analyse basée sur ' . Depot::count() . ' dépôts',
                'donnees' => $donnees,
                'source' => 'local',
            ],
        ]);
    }

    // === Méthodes privées ===

    private function getHistoriqueDepots(): array
    {
        return Depot::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['ds' => $r->date, 'y' => $r->count])
            ->toArray();
    }

    private function getHistoriqueReparations(): array
    {
        return Reparation::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['ds' => $r->date, 'y' => $r->count])
            ->toArray();
    }

    private function getHistoriqueDons(): array
    {
        return Don::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['ds' => $r->date, 'y' => $r->count])
            ->toArray();
    }

    private function getDepotsParCategorie(): array
    {
        return Depot::selectRaw('categorie, COUNT(*) as count')
            ->groupBy('categorie')
            ->orderByDesc('count')
            ->get()
            ->map(fn($r) => ['categorie' => $r->categorie, 'count' => $r->count])
            ->toArray();
    }

    private function getEvolutionMensuelle(): array
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = Carbon::now()->subMonths($i);
            $result[] = [
                'mois' => $mois->format('M Y'),
                'depots' => Depot::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count(),
                'reparations' => Reparation::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count(),
                'dons' => Don::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count(),
            ];
        }
        return $result;
    }

    private function getTauxValorisationMensuel(): array
    {
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = Carbon::now()->subMonths($i);
            $total = Depot::whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count();
            $valorises = Depot::where('statut', 'valorise')->whereYear('created_at', $mois->year)->whereMonth('created_at', $mois->month)->count();
            $result[] = [
                'mois' => $mois->format('M Y'),
                'taux' => $total > 0 ? round(($valorises / $total) * 100, 1) : 0,
            ];
        }
        return $result;
    }

    private function getBesoinsAssociations(): array
    {
        return \App\Models\Association::with('besoins')
            ->where('statut', 'active')
            ->take(10)
            ->get()
            ->map(fn($a) => ['id' => $a->id, 'nom' => $a->nom, 'besoins_count' => $a->besoins->count()])
            ->toArray();
    }

    private function getSerieTemporelle(string $type): array
    {
        $model = match($type) {
            'depot' => Depot::class,
            'reparation' => Reparation::class,
            default => Depot::class,
        };
        return $model::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function predictionSimple(string $type): array
    {
        $historique = match($type) {
            'depot' => $this->getHistoriqueDepots(),
            'reparation' => $this->getHistoriqueReparations(),
            'don' => $this->getHistoriqueDons(),
            default => [],
        };

        if (empty($historique)) {
            return ['predictions' => [], 'message' => 'Données insuffisantes'];
        }

        $values = array_column($historique, 'y');
        $moyenne = count($values) > 0 ? array_sum($values) / count($values) : 0;
        $tendance = count($values) > 1 ? ($values[count($values)-1] - $values[0]) / count($values) : 0;

        $predictions = [];
        for ($i = 1; $i <= 30; $i++) {
            $date = Carbon::now()->addDays($i)->toDateString();
            $valeur = max(0, round($moyenne + ($tendance * $i)));
            $predictions[] = [
                'date' => $date,
                'valeur_predite' => $valeur,
                'intervalle_bas' => max(0, intval($valeur * 0.8)),
                'intervalle_haut' => intval($valeur * 1.2),
            ];
        }

        return [
            'predictions' => $predictions,
            'moyenne_historique' => round($moyenne, 1),
            'tendance' => $tendance > 0 ? 'hausse' : ($tendance < 0 ? 'baisse' : 'stable'),
            'confiance' => 0.65,
        ];
    }

    private function calculerTendancesLocales(array $donnees): array
    {
        $evolution = $donnees['evolution_mensuelle'];
        $dernierMois = end($evolution);
        $avantDernierMois = prev($evolution);

        return [
            'tendances' => [
                [
                    'metrique' => 'Dépôts',
                    'direction' => $dernierMois['depots'] > $avantDernierMois['depots'] ? 'hausse' : 'baisse',
                    'pourcentage' => $avantDernierMois['depots'] > 0
                        ? round((($dernierMois['depots'] - $avantDernierMois['depots']) / $avantDernierMois['depots']) * 100, 1)
                        : 0,
                ],
            ],
            'resume' => 'Analyse des tendances sur les 6 derniers mois',
            'periode_analysee' => '6 mois',
        ];
    }

    private function genererRecommandationsRegles(array $contexte): array
    {
        $recommandations = [];

        if ($contexte['depots_en_attente'] > 20) {
            $recommandations[] = [
                'priorite' => 'haute',
                'type' => 'operationnel',
                'titre' => 'Traitement urgent des dépôts',
                'message' => "Il y a {$contexte['depots_en_attente']} dépôts en attente. Mobilisez plus d'ateliers.",
                'action' => 'Contacter les ateliers disponibles',
            ];
        }

        if ($contexte['ateliers_disponibles'] < 3) {
            $recommandations[] = [
                'priorite' => 'haute',
                'type' => 'partenariat',
                'titre' => 'Manque d\'ateliers partenaires',
                'message' => 'Recruter de nouveaux ateliers pour augmenter la capacité.',
                'action' => 'Lancer campagne de recrutement ateliers',
            ];
        }

        $recommandations[] = [
            'priorite' => 'moyenne',
            'type' => 'sensibilisation',
            'titre' => 'Promouvoir les dons aux associations',
            'message' => 'Plusieurs associations ont des besoins non couverts.',
            'action' => 'Envoyer newsletter aux utilisateurs',
        ];

        return [
            'recommandations' => $recommandations,
            'generees_a' => Carbon::now()->toISOString(),
        ];
    }
}
