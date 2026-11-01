<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValorisationRequest;
use App\Models\Depot;
use App\Models\Valorisation;
use Illuminate\Http\Request;

class ValorisationController extends Controller
{
    private function isAdmin(): bool
    {
        return auth()->user()->role === 'admin';
    }

    private function mesDepots()
    {
        $q = Depot::orderByDesc('id');
        if (!$this->isAdmin()) {
            $q->where('user_id', auth()->id());
        }
        return $q->get();
    }

    private function autoriserDepot(Depot $depot): void
    {
        abort_unless($depot->estGerablePar(auth()->user()), 403);
    }

    private function autoriser(Valorisation $valorisation): void
    {
        abort_unless($valorisation->estGerablePar(auth()->user()), 403);
    }

    public function index(Request $request)
    {
        $query = Valorisation::with('depot.user');

        if (!$this->isAdmin()) {
            $query->whereHas('depot', fn ($d) => $d->where('user_id', auth()->id()));
        }
        if ($request->filled('depot_id')) {
            $query->where('depot_id', $request->depot_id);
        }
        if ($request->filled('filiere')) {
            $query->where('filiere', $request->filiere);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return view('valorisations.index', [
            'valorisations' => $query->latest()->paginate(10)->withQueryString(),
            'filieres'      => Valorisation::FILIERES,
            'statuts'       => Valorisation::STATUTS,
            'depotFiltre'   => $request->filled('depot_id') ? Depot::find($request->depot_id) : null,
        ]);
    }

    public function create(Request $request)
    {
        return view('valorisations.create', [
            'depots'   => $this->mesDepots(),
            'depotId'  => $request->query('depot_id'),
            'filieres' => Valorisation::FILIERES,
            'statuts'  => Valorisation::STATUTS,
        ]);
    }

    public function store(ValorisationRequest $request)
    {
        $this->autoriserDepot(Depot::findOrFail($request->depot_id));

        $data = $request->validated();
        $data['statut'] = $this->isAdmin() ? ($data['statut'] ?? 'propose') : 'propose';
        $data['source'] = 'manuelle';

        Valorisation::create($data);

        return redirect()->route('valorisations.index')->with('success', 'Valorisation ajoutée.');
    }

    public function show(Valorisation $valorisation)
    {
        $this->autoriser($valorisation);
        $valorisation->load('depot.user');

        return view('valorisations.show', compact('valorisation'));
    }

    public function edit(Valorisation $valorisation)
    {
        $this->autoriser($valorisation);

        return view('valorisations.edit', [
            'valorisation' => $valorisation,
            'depots'       => $this->mesDepots(),
            'filieres'     => Valorisation::FILIERES,
            'statuts'      => Valorisation::STATUTS,
        ]);
    }

    public function update(ValorisationRequest $request, Valorisation $valorisation)
    {
        $this->autoriser($valorisation);
        $this->autoriserDepot(Depot::findOrFail($request->depot_id));

        $data = $request->validated();
        if (!$this->isAdmin()) {
            unset($data['statut']); // seul l'admin change le statut
        }

        $valorisation->update($data);

        return redirect()->route('valorisations.show', $valorisation)->with('success', 'Valorisation modifiée.');
    }

    public function destroy(Valorisation $valorisation)
    {
        $this->autoriser($valorisation);
        $valorisation->delete();

        return redirect()->route('valorisations.index')->with('success', 'Valorisation supprimée.');
    }
}