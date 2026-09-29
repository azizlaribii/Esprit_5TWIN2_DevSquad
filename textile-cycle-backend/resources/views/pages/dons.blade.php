@extends('layouts.app')

@section('title', 'Dons Textiles & Solidarité')
@section('meta_description', 'Historique des dons de vêtements redistribués aux associations caritatives')
@section('breadcrumb', 'Gestion › Dons')

@section('content')
<div class="animate-fade-in-up">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 class="page-title">Dons Textiles Solidaires</h1>
            <p class="page-subtitle">Redistribution des invendus et pièces données aux personnes dans le besoin</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('associations.index') }}" class="btn btn-secondary">
                <span class="material-icons-round">groups</span> Associations Partenaires
            </a>
            <a href="{{ route('marketplace.create') }}" class="btn btn-primary">
                <span class="material-icons-round">volunteer_activism</span> Faire un Don
            </a>
        </div>
    </div>

    <div class="card section">
        <div class="card-header">
            <div class="card-title">
                <span class="material-icons-round" style="color:var(--secondary)">volunteer_activism</span>
                Historique des dons solidaires
            </div>
            <span class="badge badge-success">{{ $dons->total() }} dons distribués</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th># ID</th>
                        <th>Donateur</th>
                        <th>Association Bénéficiaire</th>
                        <th>Poids Récolté</th>
                        <th>Statut</th>
                        <th>Date du Don</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dons as $don)
                        <tr>
                            <td>#{{ $don->id }}</td>
                            <td><strong>{{ $don->user->name ?? 'Donateur #' . $don->user_id }}</strong></td>
                            <td>{{ $don->association->nom ?? 'Association solidaire' }}</td>
                            <td><strong style="color:var(--secondary)">{{ number_format($don->quantite_kg, 1) }} kg</strong></td>
                            <td>
                                <span class="badge {{ $don->statut === 'distribue' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $don->statut)) }}
                                </span>
                            </td>
                            <td>{{ $don->created_at ? $don->created_at->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">
                                Aucun don enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dons->hasPages())
            <div style="margin-top:1.5rem;display:flex;justify-content:center">
                {{ $dons->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
