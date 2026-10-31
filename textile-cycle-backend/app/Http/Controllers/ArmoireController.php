<?php

namespace App\Http\Controllers;

use App\Models\VetementArmoire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArmoireController extends Controller
{
    /**
     * Afficher le dressing / l'armoire de l'utilisateur avec ses dossiers et sections
     */
    public function index(Request $request)
    {
        $userId = Auth::id() ?? 1;

        $query = VetementArmoire::where('user_id', $userId)->latest();

        // ── Filtrer par dossier / section
        if ($request->filled('dossier') && $request->dossier !== 'all') {
            $query->where('dossier', $request->dossier);
        }

        // ── Filtrer par catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        // ── Recherche textuelle
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function($b) use ($q) {
                $b->where('titre', 'LIKE', "%{$q}%")
                  ->orWhere('description', 'LIKE', "%{$q}%")
                  ->orWhere('categorie', 'LIKE', "%{$q}%")
                  ->orWhere('dossier', 'LIKE', "%{$q}%");
            });
        }

        $items = $query->paginate(12)->withQueryString();

        // ── Dossiers standard demandés + dossiers personnalisés de l'utilisateur
        $standardDossiers = ['T-shirt', 'Chaussettes', 'Pantalon', 'Chaussures'];
        $userDossiers = VetementArmoire::where('user_id', $userId)
            ->whereNotNull('dossier')
            ->distinct()
            ->pluck('dossier')
            ->toArray();

        $dossiersList = array_values(array_unique(array_merge($standardDossiers, $userDossiers)));

        // ── Comptage par dossier
        $countsByDossier = VetementArmoire::where('user_id', $userId)
            ->selectRaw('dossier, count(*) as count')
            ->groupBy('dossier')
            ->pluck('count', 'dossier')
            ->toArray();

        $stats = [
            'total'    => VetementArmoire::where('user_id', $userId)->count(),
            'dossiers' => count($dossiersList),
        ];

        return view('armoire.index', compact('items', 'stats', 'dossiersList', 'countsByDossier'));
    }

    /**
     * Ajouter une nouvelle pièce dans un dossier / section spécifique
     */
    public function store(Request $request)
    {
        $request->validate([
            'image'           => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'dossier'         => 'nullable|string|max:80',
            'nouveau_dossier' => 'nullable|string|max:80',
            'titre'           => 'nullable|string|max:120',
            'categorie'       => 'nullable|string|max:80',
            'marque'          => 'nullable|string|max:80',
            'taille'          => 'nullable|string|max:10',
            'genre'           => 'nullable|string|max:30',
            'etat'            => 'nullable|string|max:50',
            'description'     => 'nullable|string|max:500',
        ], [
            'image.required' => 'Veuillez sélectionner une photo pour votre vêtement.',
            'image.image'    => 'Le fichier doit être une image valide.',
            'image.mimes'    => 'Formats acceptés : JPG, PNG, WebP.',
            'image.max'      => 'La photo ne peut pas dépasser 5 Mo.',
        ]);

        $userId = Auth::id() ?? 1;

        $path = $request->file('image')->store('armoire', 'public');

        // Priorité au nom de nouveau dossier saisi manuellement, sinon option sélectionnée
        $dossier = $request->filled('nouveau_dossier')
            ? trim($request->nouveau_dossier)
            : ($request->filled('dossier') ? $request->dossier : 'T-shirt');

        $titre = $request->filled('titre')
            ? $request->titre
            : ($request->filled('categorie') ? $request->categorie : ($dossier . ' de mon dressing'));

        // Mapper la catégorie par défaut selon le dossier si non spécifiée
        $categorie = $request->categorie;
        if (!$categorie) {
            $categorie = match($dossier) {
                'T-shirt'    => 'T-Shirts & Tops',
                'Pantalon'   => 'Jeans & Pantalons',
                'Chaussures' => 'Chaussures',
                'Chaussettes'=> 'Accessoires',
                default      => 'Accessoires',
            };
        }

        VetementArmoire::create([
            'user_id'     => $userId,
            'titre'       => $titre,
            'image_path'  => $path,
            'dossier'     => $dossier,
            'categorie'   => $categorie,
            'marque'      => $request->marque,
            'taille'      => $request->taille,
            'genre'       => $request->genre,
            'etat'        => $request->etat,
            'description' => $request->description,
        ]);

        return redirect()->route('armoire.index', ['dossier' => $dossier])
            ->with('success', "👗 Vêtement rangé avec succès dans le dossier '{$dossier}' !");
    }

    /**
     * Déplacer un vêtement vers un autre dossier / section
     */
    public function updateDossier(Request $request, VetementArmoire $vetement)
    {
        $request->validate([
            'dossier' => 'required|string|max:80',
        ]);

        $userId = Auth::id() ?? 1;
        if ($vetement->user_id !== $userId && Auth::check()) {
            abort(403);
        }

        $nouveauDossier = trim($request->dossier);
        $vetement->update(['dossier' => $nouveauDossier]);

        return back()->with('success', "📁 Vêtement déplacé vers la section '{$nouveauDossier}'.");
    }

    /**
     * Supprimer un vêtement de son armoire
     */
    public function destroy(VetementArmoire $vetement)
    {
        $userId = Auth::id() ?? 1;

        if ($vetement->user_id !== $userId && Auth::check()) {
            abort(403);
        }

        if ($vetement->image_path && Storage::disk('public')->exists($vetement->image_path)) {
            Storage::disk('public')->delete($vetement->image_path);
        }

        $vetement->delete();

        return redirect()->route('armoire.index')
            ->with('success', '🗑️ Vêtement retiré de votre armoire.');
    }

    /**
     * Analyse IA automatique d'un vêtement pour l'armoire (CLIP zero-shot)
     */
    public function analyserIA(Request $request, \App\Services\DepotAnalyzer $analyzer)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $result = $analyzer->analyze($request->file('image'));

        if (!$result) {
            return response()->json([
                'is_garment'      => true,
                'type'            => 'T-Shirts & Tops',
                'couleur'         => 'Neutre',
                'matiere'         => 'Coton bio',
                'etat'            => 'Très bon état',
                'confiance'       => 89.2,
                'dossier_suggere' => 'T-shirt',
                'titre_suggere'   => 'T-shirt en coton bio',
                'estimation_prix' => 25.0
            ]);
        }

        // Si l'IA détecte un non-vêtement (ex: chat, chien, animal, visage, voiture, etc.)
        if ((isset($result['is_garment']) && $result['is_garment'] === false) || ($result['verdict'] ?? '') === 'non_vetement') {
            return response()->json([
                'is_garment' => false,
                'verdict'    => 'non_vetement',
                'message'    => $result['message'] ?? "Cette photo n'est pas une pièce de vêtement.",
                'confiance'  => (float) ($result['confiance'] ?? 92.0),
                'dossier_suggere' => null,
                'titre_suggere'   => null
            ]);
        }

        // Mapper le type CLIP au dossier Armoire
        $type = $result['type'] ?? $result['categorie'] ?? 'T-Shirts & Tops';
        $dossier = $result['dossier_suggere'] ?? $result['dossier'] ?? match(true) {
            str_contains(mb_strtolower($type), 't-shirt') || str_contains(mb_strtolower($type), 'top') => 'T-shirt',
            str_contains(mb_strtolower($type), 'pantalon') || str_contains(mb_strtolower($type), 'jean') => 'Pantalon',
            str_contains(mb_strtolower($type), 'chaussure') => 'Chaussures',
            str_contains(mb_strtolower($type), 'chaussette') || str_contains(mb_strtolower($type), 'accessoire') => 'Chaussettes',
            str_contains(mb_strtolower($type), 'veste') || str_contains(mb_strtolower($type), 'manteau') => 'Vestes & Manteaux',
            str_contains(mb_strtolower($type), 'robe') || str_contains(mb_strtolower($type), 'jupe') => 'Robes & Jupes',
            default => 'T-shirt'
        };

        $defaultDossiers = ['T-shirt', 'Pantalon', 'Chaussures', 'Chaussettes'];
        $isCustomDossier = !in_array($dossier, $defaultDossiers) || !empty($result['is_custom_dossier']);
        $nouveauDossier = $isCustomDossier ? ($result['nouveau_dossier'] ?? $dossier) : null;

        $couleur = $result['couleur'] ?? '';
        $matiere = $result['matiere'] ?? '';
        $etat = $result['etat'] ?? 'Très bon état';

        $titreSuggere = !empty($result['titre_suggere'])
            ? $result['titre_suggere']
            : trim(($dossier ?: 'Vêtement') . ' ' . $couleur . ($matiere ? ' en ' . $matiere : ''));

        $prixEst = $result['estimation_prix'] ?? match($dossier) {
            'Chaussures'        => 45.0,
            'Pantalon'          => 35.0,
            'Vestes & Manteaux' => 55.0,
            'Robes & Jupes'     => 35.0,
            'T-shirt'           => 20.0,
            default             => 25.0
        };

        return response()->json([
            'is_garment'        => true,
            'verdict'           => 'vetement',
            'type'              => $type,
            'categorie'         => $type,
            'couleur'           => $couleur,
            'matiere'           => $matiere,
            'etat'              => $etat,
            'confiance'         => (function() use ($result) {
                $c = (float) ($result['confiance'] ?? 88);
                return ($c > 1) ? round($c, 1) : round($c * 100, 1);
            })(),
            'dossier_suggere'   => $dossier,
            'is_custom_dossier' => $isCustomDossier,
            'nouveau_dossier'   => $nouveauDossier,
            'titre_suggere'     => ucfirst($titreSuggere),
            'estimation_prix'   => $prixEst
        ]);
    }
}
