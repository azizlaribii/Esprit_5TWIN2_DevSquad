<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Reparation;
use App\Models\Don;
use App\Models\Donation;
use App\Models\Transformation;
use App\Models\Atelier;
use App\Models\Association;
use App\Models\ArticleMarketplace;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardViewController extends Controller
{
    /**
     * Page principale : Tableau de bord
     */
    public function dashboard()
    {
        $kpis = [
            'depots_count' => Depot::count(),
            'reparations_count' => Reparation::count(),
            'dons_count' => Don::count(),
            'transformations_count' => Transformation::count(),
            'marketplace_count' => ArticleMarketplace::where('statut', 'disponible')->count(),
            'users_count' => User::count(),
            'eco_score' => 78,
            'kg_sauves' => 1420,
            'co2_evite' => 7450,
            'eau_economisee' => 125000,
        ];

        $recentDepots = Depot::with('user')->latest()->take(5)->get();
        $recentReparations = Reparation::with('user', 'atelier')->latest()->take(5)->get();
        $recentMarketplace = ArticleMarketplace::with('vendeur')->latest()->take(4)->get();

        return view('pages.dashboard', compact('kpis', 'recentDepots', 'recentReparations', 'recentMarketplace'));
    }

    /**
     * Page Statistiques
     */
    public function statistiques()
    {
        $stats = [
            'categories' => [
                ['nom' => 'Jeans & Pantalons', 'quantite' => 342, 'pourcentage' => 38, 'couleur' => '#6C63FF'],
                ['nom' => 'Vestes & Manteaux', 'quantite' => 254, 'pourcentage' => 28, 'couleur' => '#FF6584'],
                ['nom' => 'T-Shirts & Polos', 'quantite' => 189, 'pourcentage' => 21, 'couleur' => '#43D9AD'],
                ['nom' => 'Robes & Jupes', 'quantite' => 115, 'pourcentage' => 13, 'couleur' => '#FFA726'],
            ],
            'evolution' => [
                ['mois' => 'Jan', 'depots' => 45, 'reparations' => 28, 'dons' => 35],
                ['mois' => 'Fév', 'depots' => 52, 'reparations' => 34, 'dons' => 40],
                ['mois' => 'Mar', 'depots' => 68, 'reparations' => 42, 'dons' => 48],
                ['mois' => 'Avr', 'depots' => 74, 'reparations' => 55, 'dons' => 60],
                ['mois' => 'Mai', 'depots' => 89, 'reparations' => 61, 'dons' => 72],
                ['mois' => 'Juin', 'depots' => 110, 'reparations' => 78, 'dons' => 95],
            ],
            'impact' => [
                'co2' => '7.45 tonnes',
                'eau' => '125 000 litres',
                'pesticides' => '180 kg',
                'textile_revalorise' => '84.6%',
            ],
            'top_ateliers' => Atelier::orderByDesc('note')->take(5)->get(),
            'top_associations' => Association::orderByDesc('beneficiaires_aides')->take(5)->get(),
        ];

        return view('pages.statistiques', compact('stats'));
    }

    /**
     * Page Prédictions IA
     */
    public function predictions()
    {
        $predictions = [
            'tendances' => [
                ['titre' => 'Hausse des dépôts de manteaux (+34%)', 'type' => 'depots', 'fiabilite' => 94, 'delai' => 'Octobre-Novembre', 'conseil' => 'Augmenter la capacité de stockage hivernale.'],
                ['titre' => 'Forte demande réparations fermetures (+22%)', 'type' => 'reparations', 'fiabilite' => 88, 'delai' => 'Prochaines 3 semaines', 'conseil' => 'Alerter les ateliers partenaires spécialisés cuir & retouche.'],
                ['titre' => 'Pic de dons textiles caritatifs attendu (+45%)', 'type' => 'dons', 'fiabilite' => 91, 'delai' => 'Fin d\'année', 'conseil' => 'Coordonner les collectes avec Emmaüs et Le Relais.'],
            ],
            'anomalies' => [
                ['titre' => 'Baisse anormale réparations Lyon (-15%)', 'severite' => 'warning', 'description' => 'Possible saturation ou fermeture temporaire d\'ateliers locaux.'],
                ['titre' => 'Flux de vêtements synthétiques en hausse', 'severite' => 'info', 'description' => 'Recyclage mécanique plus complexe, orienter vers upcycling.'],
            ],
            'recommandations' => [
                'Prioriser les ateliers ayant une note > 4.8 pour les vêtements haute qualité.',
                'Proposer systématiquement l\'échange sur les pièces denim taille 38-42 très demandées.',
                'Activer les alertes dons de vêtements chauds dès la baisse de température.',
            ],
            'score_ia' => 96.8,
        ];

        return view('pages.predictions', compact('predictions'));
    }

    /**
     * Page Dépôts
     */
    public function depots()
    {
        $depots = Depot::with('user')->latest()->paginate(10);
        return view('pages.depots', compact('depots'));
    }

    /**
     * Page Réparations — Suivi unifié des réparations classiques et analyses IA intelligentes
     */
    public function reparations()
    {
        $reparations = Reparation::with('user', 'atelier')->latest()->paginate(10);
        $repairRequests = \App\Models\RepairRequest::with('workshop', 'user')->latest()->paginate(10);

        return view('pages.reparations', compact('reparations', 'repairRequests'));
    }


    /**
     * Page Dons — mélange les anciens Dons (table dons) et les nouveaux
     * Donations intelligents (table donations), triés par date décroissante.
     */
    public function dons()
    {
        // Anciens dons (table dons)
        $dons = Don::with('user', 'association')->latest()->paginate(15);

        // Nouveaux dons intelligents (table donations) — tous les utilisateurs
        $donations = Donation::with('user', 'matches.association')
            ->latest()
            ->paginate(15);

        return view('pages.dons', compact('dons', 'donations'));
    }

    /**
     * Page Ateliers
     */
    public function ateliers()
    {
        $ateliers = Atelier::orderByDesc('note')->get();
        return view('pages.ateliers', compact('ateliers'));
    }

    /**
     * Page Associations
     */
    public function associations()
    {
        $associations = Association::orderByDesc('beneficiaires_aides')->get();
        return view('pages.associations', compact('associations'));
    }
}
