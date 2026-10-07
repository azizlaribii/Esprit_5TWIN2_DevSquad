@extends('layouts.app')

@section('title', 'Détail du dépôt')
@section('breadcrumb', 'Gestion › Dépôts › Détail')

@section('content')
<div class="animate-fade-in-up">
    <h1 class="page-title">Dépôt #{{ $depot->id }}</h1>
    <p class="page-subtitle">Détail du dépôt textile</p>

    <div class="card">
        <div style="display:grid;grid-template-columns:{{ $depot->photo ? '1fr 1fr' : '1fr' }};gap:2rem;align-items:start">

            {{-- Colonne gauche : informations --}}
            <div>
                <div class="grid-2" style="margin-bottom:1.5rem">
                    <div>
                        <div class="form-label">Déposé par</div>
                        <div>{{ $depot->user->name ?? 'Utilisateur #'.$depot->user_id }}</div>
                    </div>
                    <div>
                        <div class="form-label">Catégorie</div>
                        <div>{{ $depot->categorie }}</div>
                    </div>
                    <div>
                        <div class="form-label">Quantité</div>
                        <div>{{ $depot->quantite }} pièces</div>
                    </div>
                    <div>
                        <div class="form-label">Date</div>
                        <div>{{ $depot->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="form-label">État</div>
                        <span class="badge badge-info">{{ $depot->etat }}</span>
                    </div>
                    <div>
                        <div class="form-label">Statut</div>
                        <span class="badge {{ $depot->statut === 'valide' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst(str_replace('_', ' ', $depot->statut)) }}
                        </span>
                    </div>
                </div>

                @if($depot->description)
                    <div class="form-label">Description</div>
                    <p style="margin-bottom:1.5rem">{{ $depot->description }}</p>
                @endif
                @if($depot->ai_type)
    <div class="alert alert-info" style="margin-bottom:1.5rem">
        <div>
            <span class="ai-badge">Analyse IA</span>
            <div style="margin-top:.5rem">
                Type : <strong>{{ $depot->ai_type }}</strong> ·
                Couleur : <strong>{{ $depot->ai_couleur }}</strong> ·
                Matière : <strong>{{ $depot->ai_matiere }}</strong> ·
                État : <strong>{{ $depot->ai_etat }}</strong> ·
                Confiance : <strong>{{ $depot->ai_confiance }}%</strong>
            </div>
        </div>
    </div>
@endif
<div style="display:flex;gap:.75rem;flex-wrap:wrap">
    <a href="{{ route('depots.edit', $depot) }}" class="btn btn-primary">Modifier</a>
    <a href="{{ route('depots.index') }}" class="btn btn-secondary">Retour</a>

    <form method="POST" action="{{ route('depots.destroy', $depot) }}"
          data-confirm
          data-confirm-type="danger"
          data-confirm-title="Supprimer le dépôt #{{ $depot->id }} ?"
          data-confirm-message="Vous allez supprimer définitivement le dépôt #{{ $depot->id }} ({{ $depot->categorie }}). Cette action est irréversible."
          data-confirm-ok="Oui, supprimer">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Supprimer</button>
    </form>
</div>
            </div>

            {{-- Colonne droite : photo --}}
            @if($depot->photo)
                <div>
                    <img src="{{ asset('storage/'.$depot->photo) }}"
                         style="width:100%;max-height:500px;object-fit:contain;border-radius:12px">
                </div>
            @endif

        </div>
    </div>
</div>
@endsection