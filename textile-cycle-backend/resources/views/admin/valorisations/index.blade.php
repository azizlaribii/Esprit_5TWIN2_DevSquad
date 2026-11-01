@extends('layouts.app')

@section('title', 'Administration · Valorisations')
@section('breadcrumb', 'Administration › Valorisations')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Administration des valorisations</h1>
    <p class="page-subtitle">Validez ou refusez les filières proposées par l'IA ou saisies par les utilisateurs</p>

    @include('admin._backoffice-nav')

    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                Modération des valorisations
                @if($enAttente > 0)
                    <a href="{{ route('admin.valorisations.index', ['statut' => 'propose']) }}" class="badge badge-warning" style="margin-left:.5rem">
                        {{ $enAttente }} à traiter
                    </a>
                @endif
            </div>
            <span class="badge badge-primary">{{ $valorisations->total() }} résultat(s)</span>
        </div>

        @if(!empty($depotFiltre))
            <div class="alert alert-info" style="margin-bottom:1rem;align-items:center;justify-content:space-between">
                <span>Valorisations du dépôt <strong>#{{ $depotFiltre->id }} · {{ $depotFiltre->categorie }}</strong></span>
                <a href="{{ route('admin.valorisations.index') }}" class="btn btn-secondary btn-sm">Voir toutes</a>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.valorisations.index') }}"
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
                @if(request()->hasAny(['filiere','statut','depot_id']))
                    <a href="{{ route('admin.valorisations.index') }}" class="btn btn-secondary">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th><th>Dépôt</th><th>Filière</th><th>Statut</th>
                        <th>Estimation</th><th>Source</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($valorisations as $v)
                    <tr>
                        <td>#{{ $v->id }}</td>
                        <td>
                            <a href="{{ route('admin.depots.show', $v->depot) }}">#{{ $v->depot->id }} · {{ $v->depot->categorie }}</a>
                            <div style="font-size:.75rem;color:var(--text-muted)">{{ $v->depot->user->name ?? '' }}</div>
                        </td>
                        <td><span class="badge badge-primary">{{ $filieres[$v->filiere] ?? $v->filiere }}</span></td>
                        <td>
                            <span class="badge {{ ['validee' => 'badge-success', 'realisee' => 'badge-success', 'refusee' => 'badge-danger'][$v->statut] ?? 'badge-warning' }}">
                                {{ $statuts[$v->statut] ?? $v->statut }}
                            </span>
                            @if($v->statut === 'refusee' && $v->motif_refus)
                                <div style="color:var(--accent-red);font-size:.75rem;max-width:220px">{{ $v->motif_refus }}</div>
                            @endif
                        </td>
                        <td>{{ $v->estimation_prix !== null ? number_format($v->estimation_prix, 2).' DT' : '-' }}</td>
                        <td>@if($v->source === 'ia') <span class="ai-badge">IA</span> @else Manuelle @endif</td>
                        <td>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                                @if(in_array($v->statut, ['propose', 'refusee']))
                                    <form method="POST" action="{{ route('admin.valorisations.valider', $v) }}"
                                          data-confirm
                                          data-confirm-type="warning"
                                          data-confirm-title="Valider la valorisation #{{ $v->id }} ?"
                                          data-confirm-message="Filière « {{ $filieres[$v->filiere] ?? $v->filiere }} » pour le dépôt #{{ $v->depot->id }}."
                                          data-confirm-ok="Oui, valider">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Valider</button>
                                    </form>
                                @endif

                                @if($v->statut === 'validee')
                                    <form method="POST" action="{{ route('admin.valorisations.realiser', $v) }}"
                                          data-confirm
                                          data-confirm-type="warning"
                                          data-confirm-title="Marquer la valorisation #{{ $v->id }} comme réalisée ?"
                                          data-confirm-message="La filière a bien été mise en œuvre."
                                          data-confirm-ok="Oui, réalisée">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Réalisée</button>
                                    </form>
                                @endif

                                @if(in_array($v->statut, ['propose', 'validee']))
                                    <button type="button" class="btn btn-danger btn-sm js-refuser"
                                            data-url="{{ route('admin.valorisations.refuser', $v) }}"
                                            data-title="Refuser la valorisation #{{ $v->id }} ?">Refuser</button>
                                @endif
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

@include('admin._refus-modal')
@endsection
