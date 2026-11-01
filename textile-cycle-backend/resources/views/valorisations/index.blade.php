@extends('layouts.app')

@section('title', 'Valorisations')
@section('breadcrumb', 'Gestion › Valorisations')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Valorisations</h1>
            <p class="page-subtitle">Filière de revalorisation choisie pour chaque dépôt</p>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            <a href="{{ route('depots.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">inventory_2</span> Dépôts
            </a>
            <a href="{{ route('valorisations.create', request('depot_id') ? ['depot_id' => request('depot_id')] : []) }}" class="btn btn-primary">
                <span class="material-icons-round">add_circle</span> Nouvelle valorisation
            </a>
        </div>
    </div>

    <div class="card section">
        <div class="card-header">
            <div class="card-title">Liste des valorisations</div>
            <span class="badge badge-primary">{{ $valorisations->total() }} résultat(s)</span>
        </div>

        @if(!empty($depotFiltre))
            <div class="alert alert-info" style="margin-bottom:1rem;align-items:center;justify-content:space-between">
                <span>Valorisations du dépôt <strong>#{{ $depotFiltre->id }} · {{ $depotFiltre->categorie }}</strong></span>
                <a href="{{ route('valorisations.index') }}" class="btn btn-secondary btn-sm">Voir toutes</a>
            </div>
        @endif

        <form method="GET" action="{{ route('valorisations.index') }}"
              style="display:grid;grid-template-columns:1fr 1fr auto;gap:.75rem;margin-bottom:1.25rem;align-items:end">
            @if(request('depot_id'))
                <input type="hidden" name="depot_id" value="{{ request('depot_id') }}">
            @endif
            <div>
                <label class="form-label">Filière</label>
                <select name="filiere" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($filieres as $k => $v)
                        <option value="{{ $k }}" @selected(request('filiere') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Statut</label>
                <select name="statut" class="form-control">
                    <option value="">Tous</option>
                    @foreach($statuts as $k => $v)
                        <option value="{{ $k }}" @selected(request('statut') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:.5rem">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if(request()->hasAny(['filiere','statut']))
                    <a href="{{ route('valorisations.index', request('depot_id') ? ['depot_id' => request('depot_id')] : []) }}" class="btn btn-secondary">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Dépôt</th>
                        <th>Filière</th>
                        <th>Statut</th>
                        <th>Estimation</th>
                        <th>Source</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($valorisations as $v)
                    <tr>
                        <td>#{{ $v->id }}</td>
                        <td>
                            <a href="{{ route('depots.show', $v->depot) }}">#{{ $v->depot->id }} · {{ $v->depot->categorie }}</a>
                            <div style="font-size:.75rem;color:var(--text-muted)">{{ $v->depot->user->name ?? '' }}</div>
                        </td>
                        <td><span class="badge badge-primary">{{ $filieres[$v->filiere] ?? $v->filiere }}</span></td>
                        <td>
                            <span class="badge {{ $v->statut === 'propose' ? 'badge-warning' : 'badge-success' }}">
                                {{ $statuts[$v->statut] ?? $v->statut }}
                            </span>
                        </td>
                        <td>{{ $v->estimation_prix !== null ? number_format($v->estimation_prix, 2).' DT' : '-' }}</td>
                        <td>
                            @if($v->source === 'ia') <span class="ai-badge">IA</span> @else Manuelle @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.4rem">
                                <a href="{{ route('valorisations.show', $v) }}" class="btn btn-secondary btn-sm">Voir</a>
                                <a href="{{ route('valorisations.edit', $v) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('valorisations.destroy', $v) }}"
                                      data-confirm
                                      data-confirm-type="danger"
                                      data-confirm-title="Supprimer la valorisation #{{ $v->id }} ?"
                                      data-confirm-message="Valorisation « {{ $filieres[$v->filiere] ?? $v->filiere }} » du dépôt #{{ $v->depot->id }}. Cette action est irréversible."
                                      data-confirm-ok="Oui, supprimer">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">
                            Aucune valorisation trouvée.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($valorisations->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center">
                {{ $valorisations->links('vendor.pagination.textilecycle') }}
            </div>
        @endif
    </div>

</div>
@endsection