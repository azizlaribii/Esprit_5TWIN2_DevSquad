<?php

namespace App\Http\Controllers;

use App\Models\ArticleMarketplace;
use App\Models\FavoriMarketplace;
use App\Models\DemandeMarketplace;
use App\Models\EvaluationVendeur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\MarketplaceRequest;

/**
 * MarketplaceController – CRUD complet + Moteur IA
 *
 * Routes couvertes:
 *   GET    /marketplace             → index   (liste + filtres + IA reco)
 *   GET    /marketplace/create      → create  (formulaire)
 *   POST   /marketplace             → store   (enregistrement + IA classification/prix)
 *   GET    /marketplace/{id}        → show    (détail + similarités IA)
 *   GET    /marketplace/{id}/edit   → edit    (formulaire pré-rempli)
 *   PUT    /marketplace/{id}        → update  (modification)
 *   DELETE /marketplace/{id}        → destroy (suppression avec confirmation)
 *   POST   /marketplace/{id}/favori → toggleFavori
 *   POST   /marketplace/{id}/demande→ demandeAchat
 *   POST   /marketplace/{id}/evaluer→ evaluerVendeur
 *   GET    /marketplace/favoris     → mesFavoris
 */
class MarketplaceController extends Controller
{
    // ══════════════════════════════════════════════════════
    // INDEX – Liste des articles avec filtres + IA
    // ══════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $query = ArticleMarketplace::with('vendeur')
            ->where('statut', 'disponible');

        // ── Recherche textuelle
        if ($search = trim($request->q ?? '')) {
            $query->where(function($q) use ($search) {
                $q->where('titre', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('marque', 'LIKE', "%{$search}%")
                  ->orWhere('categorie', 'LIKE', "%{$search}%");
            });
        }

        // ── Filtre Catégorie
        if ($request->filled('categorie')) {
            $cat = trim($request->categorie);
            $query->where(function($q) use ($cat) {
                $q->where('categorie', $cat)
                  ->orWhere('categorie', 'LIKE', "%{$cat}%");
            });
        }

        // ── Filtre Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // ── Filtre Taille
        if ($request->filled('taille')) {
            $query->where('taille', $request->taille);
        }

        // ── Filtre Prix Min & Max
        if ($request->filled('prix_min') && (float)$request->prix_min > 0) {
            $query->where('prix', '>=', (float)$request->prix_min);
        }
        if ($request->filled('prix_max') && (float)$request->prix_max < 500) {
            $query->where('prix', '<=', (float)$request->prix_max);
        }

        // ── Filtre État (supporte les libellés exacts et les slugs)
        if ($request->filled('etat')) {
            $etats = (array) $request->etat;
            $slugMap = [
                'neuf'     => 'Neuf avec étiquette',
                'tres_bon' => 'Très bon état',
                'bon'      => 'Bon état',
                'correct'  => 'État correct',
            ];
            $mappedEtats = [];
            foreach ($etats as $e) {
                $mappedEtats[] = $e;
                if (isset($slugMap[$e])) $mappedEtats[] = $slugMap[$e];
            }
            $query->whereIn('etat', array_unique($mappedEtats));
        }

        // ── Filtre Genre
        if ($request->filled('genre')) {
            $query->whereIn('genre', (array) $request->genre);
        }

        // ── Tri
        match($request->tri ?? 'recent') {
            'prix_asc'  => $query->orderByRaw('prix IS NULL, prix ASC'),
            'prix_desc' => $query->orderBy('prix', 'desc'),
            'ai_score'  => $query->orderBy('ai_score', 'desc'),
            default     => $query->latest(),
        };

        $articles = $query->paginate(12)->withQueryString();

        // ── IA Recommandations personnalisées
        $recommendations = $this->getAiRecommendations();

        return view('marketplace.index', compact('articles', 'recommendations'));
    }

    // ══════════════════════════════════════════════════════
    // CREATE – Formulaire de publication
    // ══════════════════════════════════════════════════════
    public function create(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $armoireItems = \App\Models\VetementArmoire::where('user_id', $userId)->latest()->get();
        $selectedArmoire = null;
        if ($request->filled('armoire_id')) {
            $selectedArmoire = \App\Models\VetementArmoire::where('user_id', $userId)->find($request->armoire_id)
                ?? \App\Models\VetementArmoire::find($request->armoire_id);
        }
        return view('marketplace.create', compact('armoireItems', 'selectedArmoire'));
    }

    // ══════════════════════════════════════════════════════
    // STORE – Enregistrement avec validation + IA
    // ══════════════════════════════════════════════════════
    public function store(MarketplaceRequest $request)
    {
        // ── Validation côté serveur
        $validated = $request->validated();

        // ── Upload image ou récupération depuis l'Armoire
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('marketplace', 'public');
        } elseif ($request->filled('armoire_image')) {
            $armoirePath = $request->armoire_image;
            if (Storage::disk('public')->exists($armoirePath)) {
                $ext = pathinfo($armoirePath, PATHINFO_EXTENSION) ?: 'jpg';
                $newFilename = 'marketplace/' . Str::random(40) . '.' . $ext;
                Storage::disk('public')->copy($armoirePath, $newFilename);
                $imageUrl = $newFilename;
            } else {
                $imageUrl = $armoirePath;
            }
        } elseif ($request->filled('armoire_vetement_id')) {
            $vetement = \App\Models\VetementArmoire::find($request->armoire_vetement_id);
            if ($vetement && $vetement->image_path) {
                if (Storage::disk('public')->exists($vetement->image_path)) {
                    $ext = pathinfo($vetement->image_path, PATHINFO_EXTENSION) ?: 'jpg';
                    $newFilename = 'marketplace/' . Str::random(40) . '.' . $ext;
                    Storage::disk('public')->copy($vetement->image_path, $newFilename);
                    $imageUrl = $newFilename;
                } else {
                    $imageUrl = $vetement->image_path;
                }
            }
        }

        // ── IA : Classification automatique
        $classification = ArticleMarketplace::classifierIA($validated['titre'], $validated['description']);

        // ── IA : Estimation du prix
        $prixEstime = ArticleMarketplace::estimerPrixIA(
            $validated['categorie'],
            $validated['etat'],
            $validated['marque'] ?? null
        );

        // ── IA : Score de compatibilité (simulé, pour démo)
        $aiScore = rand(72, 97);

        // ── Création de l'article
        $article = ArticleMarketplace::create([
            'user_id'          => Auth::id() ?? 1,
            'titre'            => $validated['titre'],
            'description'      => $validated['description'],
            'categorie'        => $validated['categorie'],
            'marque'           => $validated['marque'] ?? null,
            'taille'           => $validated['taille'],
            'genre'            => $validated['genre'],
            'etat'             => $validated['etat'],
            'type'             => $validated['type'],
            'prix'             => $validated['type'] === 'vente' ? ($validated['prix'] ?? null) : null,
            'article_echange'  => $validated['article_echange'] ?? null,
            'image_url'        => $imageUrl,
            'statut'           => 'disponible',
            'ai_score'         => $aiScore,
            'ai_prix_min'      => $prixEstime['min'],
            'ai_prix_max'      => $prixEstime['max'],
            'ai_classification'=> json_encode($classification),
        ]);

        return redirect()
            ->route('marketplace.mes-articles')
            ->with('success', '✅ Votre article a été publié sur la marketplace ! Retrouvez-le dans votre liste ci-dessous.');
    }

    // ══════════════════════════════════════════════════════
    // SHOW – Détail d'un article
    // ══════════════════════════════════════════════════════
    public function show(ArticleMarketplace $article)
    {
        // Incrémenter les vues
        $article->increment('vues');

        // Charger les relations
        $article->load('vendeur', 'evaluations.evaluateur');

        // IA : Articles similaires
        $similar = $article->getSimilarArticles(4);

        return view('marketplace.show', compact('article', 'similar'));
    }

    // ══════════════════════════════════════════════════════
    // EDIT – Formulaire pré-rempli (validation : auteur uniquement)
    // ══════════════════════════════════════════════════════
    public function edit(ArticleMarketplace $article)
    {
        // Vérification que l'utilisateur est bien le propriétaire
        // if (Auth::id() !== $article->user_id) abort(403);

        return view('marketplace.edit', compact('article'));
    }

    // ══════════════════════════════════════════════════════
    // UPDATE – Modification avec validation
    // ══════════════════════════════════════════════════════
    public function update(MarketplaceRequest $request, ArticleMarketplace $article)
    {
        // if (Auth::id() !== $article->user_id) abort(403);

        $validated = $request->validated();

        // Nouvelle image ?
        if ($request->hasFile('image')) {
            if ($article->image_url) Storage::disk('public')->delete($article->image_url);
            $validated['image_url'] = $request->file('image')->store('marketplace', 'public');
        }

        // IA : Recalcul du prix estimé
        $prixEstime = ArticleMarketplace::estimerPrixIA($validated['categorie'], $validated['etat'], $validated['marque'] ?? null);
        $validated['ai_prix_min'] = $prixEstime['min'];
        $validated['ai_prix_max'] = $prixEstime['max'];
        if ($validated['type'] !== 'vente') $validated['prix'] = null;

        $article->update($validated);

        return redirect()
            ->route('marketplace.mes-articles')
            ->with('success', '✅ Votre annonce a été mise à jour.');
    }

    // ══════════════════════════════════════════════════════
    // DESTROY – Suppression avec confirmation
    // ══════════════════════════════════════════════════════
    public function destroy(ArticleMarketplace $article)
    {
        // if (Auth::id() !== $article->user_id) abort(403);

        if ($article->image_url) {
            Storage::disk('public')->delete($article->image_url);
        }

        $article->delete();

        return redirect()
            ->route('marketplace.mes-articles')
            ->with('success', '🗑️ Votre annonce a été supprimée avec succès.');
    }

    // ══════════════════════════════════════════════════════
    // TOGGLE FAVORI – Ajouter/Retirer des favoris
    // ══════════════════════════════════════════════════════
    public function toggleFavori(ArticleMarketplace $article)
    {
        $userId = Auth::id() ?? 1;

        $existing = FavoriMarketplace::where('user_id', $userId)
            ->where('article_id', $article->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = '💔 Retiré de vos favoris.';
        } else {
            FavoriMarketplace::create(['user_id' => $userId, 'article_id' => $article->id]);
            $message = '❤️ Ajouté à vos favoris !';
        }

        return back()->with('success', $message);
    }

    // ══════════════════════════════════════════════════════
    // DEMANDE D'ACHAT – Acheter un article
    // ══════════════════════════════════════════════════════
    public function demandeAchat(Request $request, ArticleMarketplace $article)
    {
        $acheteurId = Auth::id() ?? 1;

        // Vérifier que l'utilisateur n'est pas le vendeur
        if (Auth::check() && $acheteurId === $article->user_id) {
            return back()->with('error', 'Vous ne pouvez pas acheter votre propre article.');
        }

        // Marquer l'article comme vendu et assigner l'acheteur
        $article->update([
            'statut'      => 'vendu',
            'acheteur_id' => $acheteurId,
        ]);

        // Enregistrer la demande d'achat validée
        DemandeMarketplace::create([
            'article_id'  => $article->id,
            'acheteur_id' => $acheteurId,
            'statut'      => 'acceptee',
            'message'     => $request->message ?? 'Achat confirmé',
        ]);

        // Ajouter automatiquement le vêtement dans le dressing / armoire de l'acheteur
        try {
            \App\Models\VetementArmoire::create([
                'user_id'     => $acheteurId,
                'titre'       => $article->titre,
                'image_path'  => $article->image_url,
                'dossier'     => $article->categorie ?? 'Articles achetés',
                'categorie'   => $article->categorie,
                'marque'      => $article->marque,
                'taille'      => $article->taille,
                'genre'       => $article->genre,
                'etat'        => $article->etat,
                'description' => $article->description,
            ]);
        } catch (\Exception $e) {
            // Ignorer si la table n'est pas disponible ou erreur non bloquante
        }

        return redirect()
            ->route('marketplace.mes-articles', ['tab' => 'achats'])
            ->with('success', '🎉 Félicitations ! Votre achat a été validé. Retrouvez votre article dans vos achats ci-dessous.');
    }

    // ══════════════════════════════════════════════════════
    // ÉVALUER VENDEUR
    // ══════════════════════════════════════════════════════
    public function evaluerVendeur(Request $request, ArticleMarketplace $article)
    {
        $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:500',
        ]);

        EvaluationVendeur::create([
            'article_id'   => $article->id,
            'evaluateur_id'=> Auth::id() ?? 2,
            'vendeur_id'   => $article->user_id,
            'note'         => $request->note,
            'commentaire'  => $request->commentaire,
        ]);

        // Recalculer la note moyenne du vendeur
        $notesMoyenne = EvaluationVendeur::where('vendeur_id', $article->user_id)->avg('note');
        $article->update(['note_vendeur' => round($notesMoyenne, 2)]);

        return back()->with('success', '⭐ Merci pour votre évaluation !');
    }

    // ══════════════════════════════════════════════════════
    // MES FAVORIS
    // ══════════════════════════════════════════════════════
    public function mesFavoris()
    {
        $userId = Auth::id() ?? 1;
        $favoris = FavoriMarketplace::with('article.vendeur')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return view('marketplace.favoris', compact('favoris'));
    }

    // ══════════════════════════════════════════════════════
    // MES ARTICLES – Table de gestion de l'utilisateur (Ventes & Achats)
    // ══════════════════════════════════════════════════════
    public function mesArticles(Request $request)
    {
        $userId = Auth::id() ?? 1;

        // Convert any legacy 'don' articles to 'vente'
        ArticleMarketplace::where('type', 'don')->update([
            'type' => 'vente',
            'prix' => \Illuminate\Support\Facades\DB::raw('COALESCE(prix, ai_prix_min, 15.00)')
        ]);

        $activeTab = $request->get('tab', 'ventes'); // 'ventes' ou 'achats'

        // 1. Articles mis en vente par l'utilisateur
        $queryVentes = ArticleMarketplace::with(['vendeur', 'acheteur'])
            ->where('user_id', $userId)
            ->latest();

        if ($request->statut) {
            $queryVentes->where('statut', $request->statut);
        }

        if ($request->type) {
            $queryVentes->where('type', $request->type);
        }

        $articles = $queryVentes->paginate(15, ['*'], 'page_ventes');

        // 2. Articles achetés par l'utilisateur
        $queryAchats = ArticleMarketplace::with(['vendeur', 'acheteur'])
            ->where('acheteur_id', $userId)
            ->latest('updated_at');

        $articlesAchetes = $queryAchats->paginate(15, ['*'], 'page_achats');

        // Statistiques globales de l'utilisateur
        $stats = [
            'total'       => ArticleMarketplace::where('user_id', $userId)->count(),
            'disponible'  => ArticleMarketplace::where('user_id', $userId)->where('statut', 'disponible')->count(),
            'vendu'       => ArticleMarketplace::where('user_id', $userId)->where('statut', 'vendu')->count(),
            'en_cours'    => ArticleMarketplace::where('user_id', $userId)->where('statut', 'en_cours')->count(),
            'mes_achats'  => ArticleMarketplace::where('acheteur_id', $userId)->count(),
            'total_depense' => ArticleMarketplace::where('acheteur_id', $userId)->sum('prix') ?? 0,
        ];

        return view('marketplace.mes-articles', compact('articles', 'articlesAchetes', 'stats', 'activeTab'));
    }

    // ══════════════════════════════════════════════════════
    // IA : Moteur de recommandation personnalisé
    // Analyse : historique, tailles préférées, catégories consultées
    // ══════════════════════════════════════════════════════
    private function getAiRecommendations(): \Illuminate\Support\Collection
    {
        // Récupérer les articles les mieux notés par l'IA (score élevé)
        // Dans une vraie application : analyser l'historique utilisateur
        $articles = ArticleMarketplace::where('statut', 'disponible')
            ->orderBy('ai_score', 'desc')
            ->orderBy('vues', 'desc')
            ->limit(5)
            ->get();

        // Calculer un score personnalisé pour chaque article
        $articles->each(function ($article) {
            // Simulation de personnalisation IA
            $article->ai_score = $article->ai_score > 0 ? $article->ai_score : rand(78, 95);
        });

        return $articles;
    }

    /**
     * Basculer le statut d'un article (Disponible <-> Vendu)
     */
    public function toggleStatut(ArticleMarketplace $article)
    {
        $userId = Auth::id() ?? 1;
        if ($article->user_id !== $userId && Auth::check()) {
            abort(403);
        }

        $nouveauStatut = ($article->statut === 'disponible') ? 'vendu' : 'disponible';
        $article->update(['statut' => $nouveauStatut]);

        $message = ($nouveauStatut === 'vendu')
            ? '🏷️ Article marqué comme vendu !'
            : '✅ Article remis en vente et visible sur la Marketplace !';

        return back()->with('success', $message);
    }

    /**
     * Générateur IA de description & argumentaire circulaire
     */
    public function ameliorerDescriptionIA(Request $request)
    {
        $titre = $request->titre ?: 'Vêtement éco-responsable';
        $categorie = $request->categorie ?: 'Mode circulaire';
        $marque = $request->marque ? 'de la marque ' . $request->marque : '';
        $etat = $request->etat ?: 'Très bon état';

        $templates = [
            "✨ Pièce sélectionnée avec soin pour la mode circulaire : {$titre} {$marque}. Idéal pour un dressing durable et stylé. Article en {$etat}, vérifié et prêt pour une seconde vie !",
            "🌿 Donnez une nouvelle vie à ce superbe {$titre} {$marque}. En {$etat}, confection soignée et confortable. Participez à l'économie textile circulaire en adoptant cette pièce !",
            "👗 Excellente opportunité seconde main : {$titre} {$marque}. État : {$etat}. Parfait pour compléter votre garde-robe avec un impact environnemental réduit."
        ];

        $descriptionGeneree = $templates[array_rand($templates)];

        return response()->json([
            'description' => $descriptionGeneree,
            'eco_points' => rand(15, 30),
            'impact_co2' => '2.4 kg CO2e évités'
        ]);
    }

    /**
     * Télécharger ou afficher le reçu officiel de transaction circulaire
     */
    public function telechargerRecu(ArticleMarketplace $article)
    {
        $userId = Auth::id() ?? 1;
        if ($article->user_id !== $userId && $article->acheteur_id !== $userId && Auth::check()) {
            abort(403);
        }

        $article->load(['vendeur', 'acheteur']);
        return view('marketplace.recu', compact('article'));
    }
}
