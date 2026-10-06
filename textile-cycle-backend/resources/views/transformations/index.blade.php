@extends('layouts.app')

@section('title', 'Mes projets d\'upcycling')
@section('breadcrumb', 'Gestion › Upcycling')

@section('content')
<div class="animate-fade-in-up">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1rem">
        <div>
            <h1 class="page-title">Transformation / Upcycling</h1>
            <p class="page-subtitle" style="margin-bottom:0">{{ $total }} projet(s) — transformez vos vêtements en nouveaux objets.</p>
        </div>
        <a href="{{ route('transformations.create') }}" class="btn btn-primary">
            <span class="material-icons-round">add</span> Nouveau projet
        </a>
    </div>

    {{-- Filtre par statut --}}
    <form method="GET" action="{{ route('transformations.index') }}" style="display:flex; gap:.5rem; margin-bottom:1.25rem; flex-wrap:wrap">
        <a href="{{ route('transformations.index') }}" class="btn btn-sm {{ request('statut') ? 'btn-secondary' : 'btn-primary' }}">Tous</a>
        @foreach(\App\Models\Transformation::STATUTS as $key => $label)
            <a href="{{ route('transformations.index', ['statut' => $key]) }}"
               class="btn btn-sm {{ request('statut') === $key ? 'btn-primary' : 'btn-secondary' }}">{{ $label }}</a>
        @endforeach
    </form>

    @if($transformations->isEmpty())
        <div class="card" style="text-align:center; padding:3rem">
            <span class="material-icons-round" style="font-size:3rem; color:var(--primary-light)">recycling</span>
            <h3 style="margin:.75rem 0 .5rem">Aucun projet pour le moment</h3>
            <p style="color:var(--text-secondary); margin-bottom:1.25rem">Créez votre premier projet et laissez l'IA proposer des idées.</p>
            <a href="{{ route('transformations.create') }}" class="btn btn-primary">Créer un projet</a>
        </div>
    @else
        <div class="grid-3">
            @foreach($transformations as $t)
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem; margin-bottom:.75rem">
                        <h3 style="font-size:1.05rem">{{ $t->titre }}</h3>
                        <span class="badge {{ $t->statut_badge }}">{{ $t->statut_label }}</span>
                    </div>

                    <div style="display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:.75rem">
                        <span class="badge badge-primary">{{ $t->type_projet }}</span>
                        @if($t->difficulte_label)<span class="badge badge-info">{{ $t->difficulte_label }}</span>@endif
                        @if($t->genere_par_ia)<span class="ai-badge">IA</span>@endif
                    </div>

                    @if($t->depot)
                        <div style="font-size:.8rem; color:var(--text-muted); margin-bottom:.5rem">
                            Vêtement : {{ $t->depot->categorie }} (#{{ $t->depot->id }})
                        </div>
                    @endif

                    <p style="font-size:.85rem; color:var(--text-secondary); margin-bottom:1rem">
                        {{ \Illuminate\Support\Str::limit($t->description, 90) }}
                    </p>

                    <div style="display:flex; gap:.5rem">
                        <a href="{{ route('transformations.show', $t) }}" class="btn btn-secondary btn-sm">Détails</a>
                        <a href="{{ route('transformations.edit', $t) }}" class="btn btn-secondary btn-sm">Modifier</a>
                        <form method="POST" action="{{ route('transformations.destroy', $t) }}"
                              onsubmit="return confirm('Supprimer ce projet ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
