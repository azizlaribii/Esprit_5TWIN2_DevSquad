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
        if ($search = $request->q) {
            $query->where(function($q) use ($search) {
                $q->where('titre', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('marque', 'LIKE', "%{$search}%");
            });
        }

        // ── Filtres
        if ($request->categorie) $query->where('categorie', $request->categorie);
        if ($request->type)      $query->where('type', $request->type);
        if ($request->taille)    $query->where('taille', $request->taille);
        if ($request->filled('prix_min')) $query->where('prix', '>=', $request->prix_min);
        if ($request->filled('prix_max')) $query->where('prix', '<=', $request->prix_max);
        if ($request->etat)      $query->whereIn('etat', (array) $request->etat);
        if ($request->genre)     $query->whereIn('genre', (array) $request->genre);

        // ── Tri
        match($request->tri ?? 'recent') {
            'prix_asc'  => $query->orderBy('prix', 'asc'),
            'prix_desc' => $query->orderBy('prix', 'desc'),
            'ai_score'  => $query->orderBy('ai_score', 'desc'),
            default     => $query->latest(),
        };

        $articles = $query->paginate(12);

        // ── IA Recommandations personnalisées
        $recommendations = $this->getAiRecommendations();

        return view('marketplace.index', compact('articles', 'recommendations'));
    }

    // ══════════════════════════════════════════════════════
    // CREATE – Formulaire de publication
    // ══════════════════════════════════════════════════════
    public function create()
    {
        return view('marketplace.create');
    }

    // ══════════════════════════════════════════════════════
    // STORE – Enregistrement avec validation + IA
    // ══════════════════════════════════════════════════════
    public function store(Request $request)
    {
        // ── Validation côté serveur
        $validated = $request->validate([
            'titre'          => 'required|string|min:5|max:120',
            'description'    => 'required|string|min:20|max:2000',
            'categorie'      => 'required|string|max:80',
            'marque'         => 'nullable|string|max:80',
            'taille'         => 'required|string|max:10',
            'genre'          => 'required|in:Homme,Femme,Enfant,Unisexe',
            'etat'           => 'required|string',
            'type'           => 'required|in:vente,echange,don',
            'prix'           => 'nullable|numeric|min:0|max:9999',
            'article_echange'=> 'nullable|string|max:150',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'titre.required'       => 'Le titre est obligatoire.',
            'titre.min'            => 'Le titre doit contenir au moins 5 caractères.',
            'description.required' => 'La description est obligatoire.',
            'description.min'      => 'La description doit contenir au moins 20 caractères.',
            'categorie.required'   => 'Veuillez sélectionner une catégorie.',
            'etat.required'        => 'Veuillez préciser l\'état du vêtement.',
            'type.required'        => 'Veuillez choisir un type de transaction.',
            'image.mimes'          => 'L\'image doit être au format JPEG, PNG ou WebP.',
            'image.max'            => 'L\'image ne doit pas dépasser 5 MB.',
        ]);

        // ── Upload image
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('marketplace', 'public');
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
            'prix'             => $validated['type'] !== 'don' ? ($validated['prix'] ?? null) : null,
            'article_echange'  => $validated['article_echange'] ?? null,
            'image_url'        => $imageUrl,
            'statut'           => 'disponible',
            'ai_score'         => $aiScore,
            'ai_prix_min'      => $prixEstime['min'],
            'ai_prix_max'      => $prixEstime['max'],
            'ai_classification'=> json_encode($classification),
        ]);

        return redirect()
            ->route('marketplace.show', $article)
            ->with('success', '✅ Votre article a été publié sur la marketplace !');
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
    public function update(Request $request, ArticleMarketplace $article)
    {
        // if (Auth::id() !== $article->user_id) abort(403);

        $validated = $request->validate([
            'titre'          => 'required|string|min:5|max:120',
            'description'    => 'required|string|min:20|max:2000',
            'categorie'      => 'required|string|max:80',
            'marque'         => 'nullable|string|max:80',
            'taille'         => 'required|string|max:10',
            'genre'          => 'required|in:Homme,Femme,Enfant,Unisexe',
            'etat'           => 'required|string',
            'type'           => 'required|in:vente,echange,don',
            'prix'           => 'nullable|numeric|min:0|max:9999',
            'article_echange'=> 'nullable|string|max:150',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Nouvelle image ?
        if ($request->hasFile('image')) {
            if ($article->image_url) Storage::disk('public')->delete($article->image_url);
            $validated['image_url'] = $request->file('image')->store('marketplace', 'public');
        }

        // IA : Recalcul du prix estimé
        $prixEstime = ArticleMarketplace::estimerPrixIA($validated['categorie'], $validated['etat'], $validated['marque'] ?? null);
        $validated['ai_prix_min'] = $prixEstime['min'];
        $validated['ai_prix_max'] = $prixEstime['max'];
        if ($validated['type'] === 'don') $validated['prix'] = null;

        $article->update($validated);

        return redirect()
            ->route('marketplace.show', $article)
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
            ->route('marketplace.index')
            ->with('success', '🗑️ Votre annonce a été supprimée.');
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
    // DEMANDE D'ACHAT – Faire une demande
    // ══════════════════════════════════════════════════════
    public function demandeAchat(Request $request, ArticleMarketplace $article)
    {
        $acheteurId = Auth::id() ?? 2;

        // Vérifier que l'utilisateur n'est pas le vendeur
        if ($acheteurId === $article->user_id) {
            return back()->with('error', 'Vous ne pouvez pas acheter votre propre article.');
        }

        // Vérifier doublon
        $existing = DemandeMarketplace::where('article_id', $article->id)
            ->where('acheteur_id', $acheteurId)
            ->first();

        if (!$existing) {
            DemandeMarketplace::create([
                'article_id' => $article->id,
                'acheteur_id' => $acheteurId,
                'statut' => 'en_attente',
                'message' => $request->message,
            ]);
            // Mettre à jour le statut de l'article
            $article->update(['statut' => 'en_cours']);
        }

        return back()->with('success', '🛒 Demande envoyée au vendeur avec succès !');
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
}
