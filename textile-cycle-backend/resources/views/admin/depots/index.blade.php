@extends('layouts.app')

@section('title', 'Administration · Dépôts')
@section('breadcrumb', 'Administration › Dépôts')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Administration des dépôts</h1>
    <p class="page-subtitle">Validez ou refusez les dépôts et suivez l'activité de la plateforme</p>

    @include('admin._backoffice-nav')

    {{-- ───────── Statistiques ───────── --}}
    <div class="grid-4 section">
        <div class="card">
            <div class="form-label">Dépôts</div>
            <div style="font-size:2rem;font-weight:800;font-family:'Outfit',sans-serif">{{ $stats['total'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted)">+{{ $stats['cette_semaine'] }} cette semaine</div>
        </div>
        <div class="card">
            <div class="form-label">En attente de validation</div>
            <div style="font-size:2rem;font-weight:800;font-family:'Outfit',sans-serif;color:var(--accent-orange)">{{ $stats['en_attente'] }}</div>
            <a href="{{ route('admin.depots.index', ['statut' => 'en_attente']) }}" style="font-size:.8rem">Voir les dépôts à traiter</a>
        </div>
        <div class="card">
            <div class="form-label">Pièces collectées</div>
            <div style="font-size:2rem;font-weight:800;font-family:'Outfit',sans-serif">{{ $stats['pieces'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted)">tous dépôts confondus</div>
        </div>
        <div class="card">
            <div class="form-label">Valorisations à traiter</div>
            <div style="font-size:2rem;font-weight:800;font-family:'Outfit',sans-serif;color:var(--primary-light)">{{ $stats['val_proposees'] }}</div>
            <div style="font-size:.8rem;color:var(--text-muted)">Revente validée : {{ number_format($stats['revenu'], 2) }} DT</div>
        </div>
    </div>

    <div class="grid-3 section">
        <div class="card">
            <div class="card-title" style="margin-bottom:1rem">Dépôts par statut</div>
            @php $maxStatut = max($stats['total'], 1); @endphp
            @foreach(\App\Models\Depot::STATUTS as $cle => $libelle)
                @php $n = (int) ($parStatut[$cle] ?? 0); @endphp
                <div style="margin-bottom:.9rem">
                    <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.3rem">
                        <span>{{ $libelle }}</span><strong>{{ $n }}</strong>
                    </div>
                    <div class="progress-bar"><div class="progress-fill" style="width:{{ round($n / $maxStatut * 100) }}%"></div></div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:1rem">Valorisations par filière</div>
            @php $totalFiliere = max($parFiliere->sum(), 1); @endphp
            @foreach(\App\Models\Valorisation::FILIERES as $cle => $libelle)
                @php $n = (int) ($parFiliere[$cle] ?? 0); @endphp
                <div style="margin-bottom:.9rem">
                    <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.3rem">
                        <span>{{ $libelle }}</span><strong>{{ $n }}</strong>
                    </div>
                    <div class="progress-bar"><div class="progress-fill" style="width:{{ round($n / $totalFiliere * 100) }}%"></div></div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:1rem">Catégories les plus déposées</div>
            @php $maxCat = max($parCategorie->max() ?? 0, 1); @endphp
            @forelse($parCategorie as $cat => $n)
                <div style="margin-bottom:.9rem">
                    <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.3rem">
                        <span>{{ $cat }}</span><strong>{{ $n }}</strong>
                    </div>
                    <div class="progress-bar"><div class="progress-fill" style="width:{{ round($n / $maxCat * 100) }}%"></div></div>
                </div>
            @empty
                <p style="color:var(--text-muted);font-size:.85rem">Aucune donnée.</p>
            @endforelse
        </div>
    </div>

    {{-- ───────── Liste des dépôts ───────── --}}
    <div class="card section">
        <div class="card-header">
            <div class="card-title">Modération des dépôts</div>
            <span class="badge badge-primary">{{ $depots->total() }} résultat(s)</span>
        </div>

        <form method="GET" action="{{ route('admin.depots.index') }}"
              style="display:grid;grid-template-columns:2fr 1fr auto;gap:.75rem;margin-bottom:1.25rem;align-items:end">
            <div>
                <label class="form-label">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                       placeholder="Catégorie, description, déposant...">
            </div>
            <div>
                <label class="form-label">Statut</label>
                <select name="statut" class="form-control">
                    <option value="">Tous</option>
                    @foreach(\App\Models\Depot::STATUTS as $k => $v)
                        <option value="{{ $k }}" @selected(request('statut') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:.5rem">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if(request()->hasAny(['q','statut']))
                    <a href="{{ route('admin.depots.index') }}" class="btn btn-secondary">Réinitialiser</a>
                @endif
            </div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th><th>Déposant</th><th>Catégorie</th><th>Qté</th>
                        <th>État</th><th>Statut</th><th>Valorisations</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($depots as $depot)
                    <tr>
                        <td>#{{ $depot->id }}</td>
                        <td><strong>{{ $depot->user->name ?? 'Utilisateur #'.$depot->user_id }}</strong></td>
                        <td>
                            {{ $depot->categorie }}
                            @if($depot->ai_type) <span class="ai-badge" style="margin-left:.4rem">IA</span> @endif
                        </td>
                        <td>{{ $depot->quantite }}</td>
                        <td><span class="badge badge-info">{{ $depot->etat }}</span></td>
                        <td>
                            <span class="badge {{ ['valide' => 'badge-success', 'refuse' => 'badge-danger', 'traite' => 'badge-info'][$depot->statut] ?? 'badge-warning' }}">
                                {{ \App\Models\Depot::STATUTS[$depot->statut] ?? $depot->statut }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.valorisations.index', ['depot_id' => $depot->id]) }}" class="badge badge-primary">
                                {{ $depot->valorisations_count }}
                            </a>
                        </td>
                        <td>{{ $depot->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                                <a href="{{ route('admin.depots.show', $depot) }}" class="btn btn-secondary btn-sm">Voir</a>

                                @if(in_array($depot->statut, ['en_attente', 'refuse']))
                                    <form method="POST" action="{{ route('admin.depots.valider', $depot) }}"
                                          data-confirm
                                          data-confirm-type="warning"
                                          data-confirm-title="Valider le dépôt #{{ $depot->id }} ?"
                                          data-confirm-message="Le dépôt de {{ $depot->user->name ?? 'cet utilisateur' }} ({{ $depot->categorie }}, {{ $depot->quantite }} pièces) sera marqué comme validé."
                                          data-confirm-ok="Oui, valider">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Valider</button>
                                    </form>
                                @endif

                                @if(in_array($depot->statut, ['en_attente', 'valide']))
                                    <button type="button" class="btn btn-danger btn-sm js-refuser"
                                            data-url="{{ route('admin.depots.refuser', $depot) }}"
                                            data-title="Refuser le dépôt #{{ $depot->id }} ?">Refuser</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted)">
                            Aucun dépôt ne correspond à votre recherche.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($depots->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center">
                {{ $depots->links('vendor.pagination.textilecycle') }}
            </div>
        @endif
    </div>
</div>

@include('admin._refus-modal')
@endsection
