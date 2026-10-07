@extends('layouts.app')

@section('title', 'Gestion des Dépôts Textiles')
@section('meta_description', 'Historique et statut des dépôts de vêtements effectués par les utilisateurs')
@section('breadcrumb', 'Gestion › Dépôts')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Dépôts Textiles</h1>
            <p class="page-subtitle">Suivi des points de collecte et vêtements déposés pour revalorisation</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('depots.create') }}" class="btn btn-primary">
                <span class="material-icons-round">add_circle</span> Nouveau dépôt
            </a>
        </div>
    </div>

    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--accent-orange)">inventory_2</span>
                Liste des dépôts enregistrés
            </div>
                <span class="badge badge-primary">{{ $depots->total() }} résultat(s)</span>
        </div>

        <form method="GET" action="{{ route('depots.index') }}"
      style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:.75rem;margin-bottom:1.25rem;align-items:end">

    <div>
        <label class="form-label">Recherche</label>
        <input type="text" name="q" class="form-control" value="{{ request('q') }}"
               placeholder="Catégorie, description, déposant...">
    </div>

    <div>
        <label class="form-label">Catégorie</label>
        <select name="categorie" class="form-control">
            <option value="">Toutes</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" @selected(request('categorie') === $c)>{{ $c }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="form-label">État</label>
        <select name="etat" class="form-control">
            <option value="">Tous</option>
            @foreach($etats as $e)
                <option value="{{ $e }}" @selected(request('etat') === $e)>{{ $e }}</option>
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
        <button type="submit" class="btn btn-primary">
            <span class="material-icons-round" style="font-size:1.1rem">search</span> Filtrer
        </button>
        @if(request()->hasAny(['q','categorie','etat','statut']))
            <a href="{{ route('depots.index') }}" class="btn btn-secondary">Réinitialiser</a>
        @endif
    </div>
</form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Utilisateur</th>
                        <th>Catégorie</th>
                        <th>Quantité</th>
                        <th>État Initial</th>
                        <th>Statut</th>
                        <th>Date de Dépôt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depots as $depot)
                        <tr>
                            <td>#{{ $depot->id }}</td>
                            <td><strong>{{ $depot->user->name ?? 'Utilisateur #' . $depot->user_id }}</strong></td>
                            <td>
    {{ $depot->categorie }}
    @if($depot->ai_type)
        <span class="ai-badge" style="margin-left:.4rem">IA</span>
    @endif
</td>
                            <td>{{ $depot->quantite }} pièces</td>
                            <td>
                                <span class="badge badge-info">{{ $depot->etat }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $depot->statut === 'valide' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $depot->statut)) }}
                                </span>
                            </td>
                            <td>{{ $depot->created_at ? $depot->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <div style="display:flex;gap:.4rem">
                                    <a href="{{ route('depots.show', $depot) }}" class="btn btn-secondary btn-sm">Voir</a>
                                    <a href="{{ route('depots.edit', $depot) }}" class="btn btn-secondary btn-sm">Modifier</a>
<form method="POST" action="{{ route('depots.destroy', $depot) }}"
      data-confirm
      data-confirm-type="danger"
      data-confirm-title="Supprimer le dépôt #{{ $depot->id }} ?"
      data-confirm-message="Vous allez supprimer définitivement le dépôt #{{ $depot->id }} ({{ $depot->categorie }}, {{ $depot->quantite }} pièces). Cette action est irréversible."
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
                            <td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">
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
@endsection