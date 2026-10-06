@extends('layouts.app')

@section('title', $transformation->titre)
@section('breadcrumb', 'Gestion › Upcycling › Détail')

@section('content')
<div class="animate-fade-in-up" style="max-width:900px; margin:0 auto">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1rem">
        <h1 class="page-title" style="margin-bottom:0">{{ $transformation->titre }}</h1>
        <a href="{{ route('transformations.index') }}" class="btn btn-secondary">
            <span class="material-icons-round">arrow_back</span> Retour
        </a>
    </div>

    <div class="card">
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.25rem">
            <span class="badge {{ $transformation->statut_badge }}">{{ $transformation->statut_label }}</span>
            <span class="badge badge-primary">{{ $transformation->type_projet }}</span>
            @if($transformation->difficulte_label)<span class="badge badge-info">Difficulté : {{ $transformation->difficulte_label }}</span>@endif
            @if($transformation->duree_estimee)<span class="badge badge-warning">⏱ {{ $transformation->duree_estimee }}</span>@endif
            @if($transformation->genere_par_ia)<span class="ai-badge">Idée IA</span>@endif
        </div>

        <h3 style="font-size:.9rem; color:var(--text-muted); margin-bottom:.4rem">Description</h3>
        <p style="color:var(--text-secondary); margin-bottom:1.25rem">{{ $transformation->description ?: 'Aucune description.' }}</p>

        <h3 style="font-size:.9rem; color:var(--text-muted); margin-bottom:.4rem">Matériaux</h3>
        @if(!empty($transformation->materiaux))
            <ul style="color:var(--text-secondary); margin:0 0 1.25rem 1.25rem">
                @foreach($transformation->materiaux as $m)<li>{{ $m }}</li>@endforeach
            </ul>
        @else
            <p style="color:var(--text-secondary); margin-bottom:1.25rem">Non précisés.</p>
        @endif

        {{-- Relation Eloquent exploitée : le dépôt lié --}}
        <h3 style="font-size:.9rem; color:var(--text-muted); margin-bottom:.4rem">Vêtement d'origine</h3>
        <p style="color:var(--text-secondary); margin-bottom:1.5rem">
            @if($transformation->depot)
                {{ $transformation->depot->categorie }} — état : {{ $transformation->depot->etat }} (dépôt #{{ $transformation->depot->id }})
            @else
                Aucun vêtement lié.
            @endif
        </p>

        <div style="display:flex; gap:.75rem">
            <a href="{{ route('transformations.edit', $transformation) }}" class="btn btn-primary">
                <span class="material-icons-round">edit</span> Modifier
            </a>
            <form method="POST" action="{{ route('transformations.destroy', $transformation) }}"
                  onsubmit="return confirm('Supprimer définitivement ce projet ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <span class="material-icons-round">delete</span> Supprimer
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
