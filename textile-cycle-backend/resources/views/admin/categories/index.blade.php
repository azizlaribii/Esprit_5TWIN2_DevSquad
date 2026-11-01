@extends('layouts.app')

@section('title', 'Catégories d\'upcycling')
@section('breadcrumb', 'Administration › Upcycling › Catégories')

@section('content')
<div class="animate-fade-in-up">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem">
        <div>
            <h1 class="page-title">Catégories d'upcycling</h1>
            <p class="page-subtitle" style="margin-bottom:0">BackOffice : gérez les types de projets proposés aux utilisateurs.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <span class="material-icons-round">add</span> Nouvelle catégorie
        </a>
    </div>

    @if($errors->has('suppression'))
        <div class="alert alert-danger" style="margin-bottom:1rem">{{ $errors->first('suppression') }}</div>
    @endif

    <div class="grid-3" style="margin-bottom:1.25rem">
        <div class="card"><div style="font-size:.8rem; color:var(--text-muted)">Catégories</div>
            <div style="font-size:1.8rem; font-weight:800">{{ $categories->count() }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:var(--text-muted)">Actives</div>
            <div style="font-size:1.8rem; font-weight:800; color:var(--secondary)">{{ $categories->where('actif', true)->count() }}</div></div>
        <div class="card"><div style="font-size:.8rem; color:var(--text-muted)">Projets au total</div>
            <div style="font-size:1.8rem; font-weight:800; color:var(--primary-light)">{{ $totalProjets }}</div></div>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Catégorie</th><th>Description</th><th>Projets</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td style="color:var(--text-primary); font-weight:600">
                            <span class="material-icons-round" style="vertical-align:middle; color:var(--primary-light)">{{ $c->icone }}</span>
                            {{ $c->nom }}
                        </td>
                        <td>{{ $c->description }}</td>
                        <td><span class="badge badge-primary">{{ $c->transformations_count }}</span></td>
                        <td>
                            <span class="badge {{ $c->actif ? 'badge-success' : 'badge-warning' }}">{{ $c->actif ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                            <div style="display:flex; gap:.4rem">
                                <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $c) }}"
                                      onsubmit="return confirm('Supprimer la catégorie « {{ $c->nom }} » ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center">Aucune catégorie.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
