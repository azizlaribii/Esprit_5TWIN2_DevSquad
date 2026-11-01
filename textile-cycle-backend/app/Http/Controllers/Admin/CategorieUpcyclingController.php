<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategorieUpcyclingRequest;
use App\Models\CategorieUpcycling;
use App\Models\Transformation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** BackOffice : l'administrateur gère les catégories d'upcycling. */
class CategorieUpcyclingController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories'    => CategorieUpcycling::withCount('transformations')->orderBy('nom')->get(),
            'totalProjets'  => Transformation::count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(CategorieUpcyclingRequest $request): RedirectResponse
    {
        CategorieUpcycling::create($this->payload($request));

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée.');
    }

    public function edit(CategorieUpcycling $categorie): View
    {
        return view('admin.categories.edit', ['categorie' => $categorie]);
    }

    public function update(CategorieUpcyclingRequest $request, CategorieUpcycling $categorie): RedirectResponse
    {
        $categorie->update($this->payload($request));

        // Garde le champ texte historique « type_projet » synchronisé avec le nom
        $categorie->transformations()->update(['type_projet' => $categorie->nom]);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(CategorieUpcycling $categorie): RedirectResponse
    {
        if ($categorie->transformations()->exists()) {
            return redirect()->route('admin.categories.index')
                ->withErrors(['suppression' => "Impossible de supprimer « {$categorie->nom} » : des projets l'utilisent. Désactivez-la plutôt."]);
        }

        $categorie->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée.');
    }

    private function payload(CategorieUpcyclingRequest $request): array
    {
        $data = $request->validated();
        $data['icone'] = $data['icone'] ?? 'category';
        $data['actif'] = $request->boolean('actif');

        return $data;
    }
}
