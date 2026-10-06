<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransformationRequest;
use App\Models\Depot;
use App\Models\Transformation;
use App\Services\UpcyclingIdeaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransformationController extends Controller
{
    public function index(Request $request): View
    {
        $transformations = Transformation::with('depot')
            ->where('user_id', $request->user()->id)
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->input('statut')))
            ->latest()
            ->get();

        return view('transformations.index', [
            'transformations' => $transformations,
            'total'           => Transformation::where('user_id', $request->user()->id)->count(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('transformations.create', ['depots' => $this->depotsOf($request)]);
    }

    public function store(TransformationRequest $request): RedirectResponse
    {
        $transformation = Transformation::create($this->payload($request) + ['user_id' => $request->user()->id]);

        return redirect()->route('transformations.show', $transformation)
            ->with('success', "Projet d'upcycling créé avec succès.");
    }

    public function show(Request $request, Transformation $transformation): View
    {
        $this->authorizeOwner($request, $transformation);

        return view('transformations.show', ['transformation' => $transformation->load('depot')]);
    }

    public function edit(Request $request, Transformation $transformation): View
    {
        $this->authorizeOwner($request, $transformation);

        return view('transformations.edit', [
            'transformation' => $transformation,
            'depots'         => $this->depotsOf($request),
        ]);
    }

    public function update(TransformationRequest $request, Transformation $transformation): RedirectResponse
    {
        $this->authorizeOwner($request, $transformation);

        $transformation->update($this->payload($request));

        return redirect()->route('transformations.show', $transformation)
            ->with('success', 'Projet mis à jour.');
    }

    public function destroy(Request $request, Transformation $transformation): RedirectResponse
    {
        $this->authorizeOwner($request, $transformation);

        $transformation->delete();

        return redirect()->route('transformations.index')
            ->with('success', 'Projet supprimé.');
    }

    /** Endpoint AJAX : l'IA propose des idées de transformation. */
    public function suggest(Request $request, UpcyclingIdeaService $ai): JsonResponse
    {
        $request->validate([
            'depot_id' => ['nullable', 'integer'],
            'garment'  => ['nullable', 'string', 'max:150', 'required_without:depot_id'],
        ], [
            'garment.required_without' => 'Choisissez un vêtement ou décrivez-le.',
        ]);

        $depot = $request->filled('depot_id')
            ? Depot::where('user_id', $request->user()->id)->find($request->input('depot_id'))
            : null;

        $garment = trim(($depot ? $depot->categorie : '') . ' ' . $request->input('garment', ''));

        if ($garment === '') {
            return response()->json(['message' => 'Vêtement introuvable.'], 422);
        }

        return response()->json($ai->suggest($garment, $depot?->etat));
    }

    // ---------------------------------------------------------------------

    private function payload(TransformationRequest $request): array
    {
        $data = $request->validated();

        $data['materiaux'] = collect(explode(',', $data['materiaux'] ?? ''))
            ->map(fn ($m) => trim($m))
            ->filter()
            ->values()
            ->all();

        $data['genere_par_ia'] = $request->boolean('genere_par_ia');

        return $data;
    }

    private function depotsOf(Request $request)
    {
        return Depot::where('user_id', $request->user()->id)->orderBy('categorie')->get();
    }

    private function authorizeOwner(Request $request, Transformation $transformation): void
    {
        abort_unless(
            $transformation->user_id === $request->user()->id || $request->user()->isAdmin(),
            403
        );
    }
}
