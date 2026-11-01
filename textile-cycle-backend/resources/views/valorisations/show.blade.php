@extends('layouts.app')

@section('title', 'Détail de la valorisation')
@section('breadcrumb', 'Gestion › Valorisations › Détail')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Valorisation #{{ $valorisation->id }}</h1>

    <div class="card">
        <div class="grid-2" style="margin-bottom:1.5rem">
            <div>
                <div class="form-label">Dépôt concerné</div>
                <a href="{{ route('depots.show', $valorisation->depot) }}">
                    #{{ $valorisation->depot->id }} · {{ $valorisation->depot->categorie }}
                </a>
            </div>
            <div>
                <div class="form-label">Déposant</div>
                <div>{{ $valorisation->depot->user->name ?? '-' }}</div>
            </div>
            <div>
                <div class="form-label">Filière</div>
                <span class="badge badge-primary">{{ \App\Models\Valorisation::FILIERES[$valorisation->filiere] ?? $valorisation->filiere }}</span>
            </div>
            <div>
                <div class="form-label">Statut</div>
                <span class="badge {{ $valorisation->statut === 'propose' ? 'badge-warning' : 'badge-success' }}">
                    {{ \App\Models\Valorisation::STATUTS[$valorisation->statut] ?? $valorisation->statut }}
                </span>
            </div>
            <div>
                <div class="form-label">Estimation</div>
                <div>{{ $valorisation->estimation_prix !== null ? number_format($valorisation->estimation_prix, 2).' DT' : '-' }}</div>
            </div>
            <div>
                <div class="form-label">Source</div>
                @if($valorisation->source === 'ia')
                    <span class="ai-badge">Proposée par l'IA</span>
                @else
                    <span class="badge badge-info">Manuelle</span>
                @endif
            </div>
        </div>

        @if($valorisation->commentaire)
            <div class="form-label">Commentaire</div>
            <p style="margin-bottom:1.5rem">{{ $valorisation->commentaire }}</p>
        @endif


@if($valorisation->statut === 'refusee' && $valorisation->motif_refus)
    <div class="alert alert-danger" style="margin-bottom:1.5rem">
        <div><strong>Valorisation refusée</strong><div>{{ $valorisation->motif_refus }}</div></div>
    </div>
@endif


        <div style="display:flex;gap:.75rem;flex-wrap:wrap">
            <a href="{{ route('valorisations.edit', $valorisation) }}" class="btn btn-primary">Modifier</a>
            <a href="{{ route('valorisations.index') }}" class="btn btn-secondary">Retour</a>
            <form method="POST" action="{{ route('valorisations.destroy', $valorisation) }}"
                  data-confirm
                  data-confirm-type="danger"
                  data-confirm-title="Supprimer la valorisation #{{ $valorisation->id }} ?"
                  data-confirm-message="Cette action est irréversible."
                  data-confirm-ok="Oui, supprimer">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </form>
        </div>
    </div>
</div>
@endsection