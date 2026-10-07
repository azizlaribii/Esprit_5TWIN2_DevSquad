<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Services\DepotAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepotController extends Controller
{
    private const ETATS = ['Très bon état', 'Bon état', 'A réparer', 'Usé'];
    private const STATUTS = ['en_attente', 'valide', 'traite'];

    private function rules(): array
    {
        return [
            'categorie'    => 'required|string|min:3|max:100',
            'quantite'     => 'required|integer|min:1|max:1000',
            'etat'         => ['required', Rule::in(self::ETATS)],
            'statut'       => ['nullable', Rule::in(self::STATUTS)],
            'description'  => 'nullable|string|max:1000',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'ai_type'      => 'nullable|string|max:100',
            'ai_couleur'   => 'nullable|string|max:50',
            'ai_etat'      => 'nullable|string|max:50',
            'ai_matiere'   => 'nullable|string|max:50',
            'ai_confiance' => 'nullable|numeric|min:0|max:100',
        ];
    }

    private function messages(): array
    {
        return [
            'categorie.required' => 'La catégorie est obligatoire.',
            'categorie.min'      => 'La catégorie doit contenir au moins 3 caractères.',
            'quantite.required'  => 'La quantité est obligatoire.',
            'quantite.integer'   => 'La quantité doit être un nombre entier.',
            'quantite.min'       => 'La quantité doit être au moins 1.',
            'etat.required'      => "L'état est obligatoire.",
            'etat.in'            => "L'état choisi n'est pas valide.",
            'photo.image'        => 'Le fichier doit être une image.',
            'photo.max'          => "L'image ne doit pas dépasser 5 Mo.",
        ];
    }

public function index(Request $request)
{
    $query = Depot::with('user');

    // Recherche texte : catégorie, description, nom du déposant
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('categorie', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
        });
    }

    if ($request->filled('etat')) {
        $query->where('etat', $request->etat);
    }

    if ($request->filled('statut')) {
        $query->where('statut', $request->statut);
    }

    if ($request->filled('categorie')) {
        $query->where('categorie', $request->categorie);
    }

    // Liste des catégories existantes pour le menu déroulant
    $categories = Depot::select('categorie')->distinct()->orderBy('categorie')->pluck('categorie');

    $depots = $query->latest()->paginate(10)->withQueryString();

    return view('depots.index', [
        'depots'     => $depots,
        'categories' => $categories,
        'etats'      => self::ETATS,
        'statuts'    => ['en_attente' => 'En attente', 'valide' => 'Validé', 'traite' => 'Traité'],
    ]);
}

    public function create()
    {
        return view('depots.create');
    }

    public function analyser(Request $request, DepotAnalyzer $analyzer)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $result = $analyzer->analyze($request->file('photo'));

        if (!$result) {
            return response()->json(
                ['message' => 'Service IA indisponible. Remplissez le formulaire manuellement.'],
                503
            );
        }

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());
        $data['user_id'] = auth()->id();
        $data['statut']  = $data['statut'] ?? 'en_attente';

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('depots', 'public');
        }

        Depot::create($data);

        return redirect()->route('depots.index')->with('success', 'Dépôt ajouté avec succès.');
    }

    public function show(Depot $depot)
    {
        $depot->load('user');
        return view('depots.show', compact('depot'));
    }

    public function edit(Depot $depot)
    {
        return view('depots.edit', compact('depot'));
    }

    public function update(Request $request, Depot $depot)
    {
        $data = $request->validate($this->rules(), $this->messages());

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('depots', 'public');
        }

        $depot->update($data);

        return redirect()->route('depots.show', $depot)->with('success', 'Dépôt modifié avec succès.');
    }

    public function destroy(Depot $depot)
    {
        $depot->delete();
        return redirect()->route('depots.index')->with('success', 'Dépôt supprimé.');
    }
}