<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\Valorisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepotAdminController extends Controller
{
    /** Qui a traité l'élément et quand. */
    private function tracer(): array
    {
        return ['traite_par' => auth()->id(), 'traite_le' => now()];
    }

    private function regleMotif(): array
    {
        return ['motif_refus' => 'required|string|min:5|max:500'];
    }

    private function messagesMotif(): array
    {
        return [
            'motif_refus.required' => 'Le motif du refus est obligatoire.',
            'motif_refus.min'      => 'Le motif doit contenir au moins 5 caractères.',
            'motif_refus.max'      => 'Le motif ne doit pas dépasser 500 caractères.',
        ];
    }

    // ───────────────────────── DÉPÔTS ─────────────────────────

    /** Tableau de bord + liste des dépôts. */
    public function index(Request $request)
    {
        $parStatut = Depot::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')->pluck('total', 'statut');

        $parFiliere = Valorisation::select('filiere', DB::raw('count(*) as total'))
            ->groupBy('filiere')->pluck('total', 'filiere');

        $parCategorie = Depot::select('categorie', DB::raw('count(*) as total'))
            ->groupBy('categorie')->orderByDesc('total')->limit(6)->pluck('total', 'categorie');

        $stats = [
            'total'         => Depot::count(),
            'en_attente'    => (int) ($parStatut['en_attente'] ?? 0),
            'pieces'        => (int) Depot::sum('quantite'),
            'cette_semaine' => Depot::where('created_at', '>=', now()->subDays(7))->count(),
            'val_proposees' => Valorisation::where('statut', 'propose')->count(),
            'revenu'        => (float) Valorisation::where('filiere', 'revente')
                                    ->whereIn('statut', ['validee', 'realisee'])->sum('estimation_prix'),
        ];

        $query = Depot::with('user')->withCount('valorisations');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('categorie', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Les dépôts en attente passent en premier
        $depots = $query
            ->orderByRaw("CASE WHEN statut = 'en_attente' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.depots.index', [
            'depots'       => $depots,
            'stats'        => $stats,
            'parStatut'    => $parStatut,
            'parFiliere'   => $parFiliere,
            'parCategorie' => $parCategorie,
        ]);
    }

    public function show(Depot $depot)
    {
        $depot->load('user', 'valorisations', 'traitePar');

        return view('admin.depots.show', compact('depot'));
    }

    public function valider(Depot $depot)
    {
        $depot->update(['statut' => 'valide', 'motif_refus' => null] + $this->tracer());

        return back()->with('success', "Dépôt #{$depot->id} validé.");
    }

    public function refuser(Request $request, Depot $depot)
    {
        $data = $request->validate($this->regleMotif(), $this->messagesMotif());

        // Refuser un dépôt refuse aussi ses valorisations encore « proposées »
        DB::transaction(function () use ($depot, $data) {
            $depot->update(['statut' => 'refuse', 'motif_refus' => $data['motif_refus']] + $this->tracer());

            $depot->valorisations()->where('statut', 'propose')->update([
                'statut'      => 'refusee',
                'motif_refus' => 'Dépôt refusé : ' . $data['motif_refus'],
            ] + $this->tracer());
        });

        return back()->with('success', "Dépôt #{$depot->id} refusé.");
    }

    public function traiter(Depot $depot)
    {
        if ($depot->statut !== 'valide') {
            return back()->withErrors(['statut' => 'Seul un dépôt validé peut être marqué comme traité.']);
        }

        $depot->update(['statut' => 'traite'] + $this->tracer());

        return back()->with('success', "Dépôt #{$depot->id} marqué comme traité.");
    }

    // ───────────────────────── VALORISATIONS ─────────────────────────

    public function valorisations(Request $request)
    {
        $query = Valorisation::with('depot.user');

        if ($request->filled('depot_id')) {
            $query->where('depot_id', $request->depot_id);
        }
        if ($request->filled('filiere')) {
            $query->where('filiere', $request->filiere);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $valorisations = $query
            ->orderByRaw("CASE WHEN statut = 'propose' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.valorisations.index', [
            'valorisations' => $valorisations,
            'filieres'      => Valorisation::FILIERES,
            'statuts'       => Valorisation::STATUTS,
            'enAttente'     => Valorisation::where('statut', 'propose')->count(),
            'depotFiltre'   => $request->filled('depot_id') ? Depot::find($request->depot_id) : null,
        ]);
    }

    public function validerValorisation(Valorisation $valorisation)
    {
        $valorisation->update(['statut' => 'validee', 'motif_refus' => null] + $this->tracer());

        return back()->with('success', "Valorisation #{$valorisation->id} validée.");
    }

    public function refuserValorisation(Request $request, Valorisation $valorisation)
    {
        $data = $request->validate($this->regleMotif(), $this->messagesMotif());

        $valorisation->update(['statut' => 'refusee', 'motif_refus' => $data['motif_refus']] + $this->tracer());

        return back()->with('success', "Valorisation #{$valorisation->id} refusée.");
    }

    public function realiserValorisation(Valorisation $valorisation)
    {
        if ($valorisation->statut !== 'validee') {
            return back()->withErrors(['statut' => 'Seule une valorisation validée peut être marquée comme réalisée.']);
        }

        $valorisation->update(['statut' => 'realisee'] + $this->tracer());

        return back()->with('success', "Valorisation #{$valorisation->id} marquée comme réalisée.");
    }
}
