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
            <a href="{{ route('marketplace.create') }}" class="btn btn-primary">
                <span class="material-icons-round">add_circle</span> Déposer ou Publier
            </a>
        </div>
    </div>

    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--accent-orange)">inventory_2</span>
                Liste des dépôts enregistrés
            </div>
            <span class="badge badge-primary">{{ $depots->total() }} dépôts au total</span>
        </div>

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
                    </tr>
                </thead>
                <tbody>
                    @forelse($depots as $depot)
                        <tr>
                            <td>#{{ $depot->id }}</td>
                            <td><strong>{{ $depot->user->name ?? 'Utilisateur #' . $depot->user_id }}</strong></td>
                            <td>{{ $depot->categorie }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">
                                Aucun dépôt trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($depots->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center">
                {{ $depots->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
